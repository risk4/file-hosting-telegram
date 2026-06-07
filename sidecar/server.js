/**
 * TeleStore Sidecar — Node.js GramJS Service
 * Dijalankan terpisah dari Laravel, berkomunikasi via HTTP internal.
 * Hanya menerima request dari Laravel (dilindungi X-Sidecar-Secret).
 */

require('dotenv').config({ path: '../.env' });

const express = require('express');
const multer  = require('multer');
const fs      = require('fs');
const path    = require('path');

const { TelegramClient } = require('telegram');
const { StringSession }  = require('telegram/sessions');

const app    = express();
const PORT   = process.env.SIDECAR_PORT || 3001;
const SECRET = process.env.SIDECAR_SECRET || '';

app.use(express.json());

// ── Upload storage ──────────────────────────────────────
const upload = multer({
    dest: path.join(__dirname, 'tmp'),
    limits: { fileSize: 2 * 1024 * 1024 * 1024 } // 2GB
});

if (!fs.existsSync(path.join(__dirname, 'tmp'))) {
    fs.mkdirSync(path.join(__dirname, 'tmp'));
}

// ── Session store ───────────────────────────────────────
const SESSION_FILE = path.join(__dirname, '.session');

function loadSession() {
    try {
        if (fs.existsSync(SESSION_FILE)) {
            return fs.readFileSync(SESSION_FILE, 'utf-8').trim();
        }
    } catch {}
    return '';
}

function saveSession(str) {
    fs.writeFileSync(SESSION_FILE, str);
}

// ── Auth middleware ─────────────────────────────────────
function auth(req, res, next) {
    if (SECRET && req.headers['x-sidecar-secret'] !== SECRET) {
        return res.status(401).json({ error: 'Unauthorized' });
    }
    next();
}

// ── Client state ────────────────────────────────────────
let client      = null;
let loginState  = {}; // temporary login state per session

// ── Auto-reconnect ──────────────────────────────────────
async function autoConnect() {
    const sessionStr = loadSession();
    const apiId      = parseInt(process.env.TELEGRAM_API_ID || '0');
    const apiHash    = process.env.TELEGRAM_API_HASH || '';

    if (!sessionStr || !apiId || !apiHash) {
        console.log('⚠️  Belum ada sesi tersimpan. Login via Admin → Pengaturan.');
        return;
    }

    try {
        console.log('🔄 Mencoba reconnect sesi tersimpan...');
        const session = new StringSession(sessionStr);
        client = new TelegramClient(session, apiId, apiHash, { connectionRetries: 5 });
        await client.connect();

        if (await client.isUserAuthorized()) {
            const me = await client.getMe();
            console.log(`✅ Auto-login: ${me.firstName} (@${me.username || me.phone})`);
        } else {
            console.log('⚠️  Sesi tidak valid.');
            client = null;
        }
    } catch (e) {
        console.log('⚠️  Auto-reconnect gagal:', e.message);
        client = null;
    }
}

// ════════════════════════════════════════════════════════
// ROUTES
// ════════════════════════════════════════════════════════

// ── GET /api/status ─────────────────────────────────────
app.get('/api/status', auth, async (req, res) => {
    if (!client || !client.connected) {
        return res.json({ connected: false });
    }
    try {
        const ok = await client.isUserAuthorized();
        if (!ok) return res.json({ connected: false });
        const me = await client.getMe();
        res.json({
            connected: true,
            user: {
                id:        me.id?.toString(),
                firstName: me.firstName,
                lastName:  me.lastName || '',
                username:  me.username || '',
                phone:     me.phone,
            }
        });
    } catch (e) {
        res.json({ connected: false, error: e.message });
    }
});

// ── POST /api/login/start ───────────────────────────────
app.post('/api/login/start', auth, async (req, res) => {
    const { apiId, apiHash, phone } = req.body;
    if (!apiId || !apiHash || !phone) {
        return res.status(400).json({ error: 'apiId, apiHash, dan phone wajib.' });
    }

    try {
        const session = new StringSession(loadSession());
        client = new TelegramClient(session, parseInt(apiId), apiHash, { connectionRetries: 5 });
        await client.connect();

        if (await client.isUserAuthorized()) {
            const str = client.session.save();
            saveSession(str);
            const me = await client.getMe();
            return res.json({
                status: 'already_logged_in',
                user: { firstName: me.firstName, username: me.username }
            });
        }

        const result = await client.sendCode({ apiId: parseInt(apiId), apiHash }, phone);
        loginState = { apiId: parseInt(apiId), apiHash, phone, phoneCodeHash: result.phoneCodeHash };

        res.json({ status: 'code_sent', phoneCodeHash: result.phoneCodeHash });
    } catch (e) {
        console.error('login/start error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/login/verify ──────────────────────────────
app.post('/api/login/verify', auth, async (req, res) => {
    if (!client) return res.status(400).json({ error: 'Mulai login dulu.' });

    const { code, phoneCodeHash } = req.body;

    try {
        await client.invoke(
            new (require('telegram/tl').Api.auth.SignIn)({
                phoneNumber:   loginState.phone,
                phoneCodeHash: phoneCodeHash || loginState.phoneCodeHash,
                phoneCode:     code,
            })
        );

        const str = client.session.save();
        saveSession(str);
        const me = await client.getMe();
        res.json({ status: 'success', user: { firstName: me.firstName, username: me.username } });

    } catch (e) {
        if (e.errorMessage === 'SESSION_PASSWORD_NEEDED') {
            return res.json({ status: 'need_2fa' });
        }
        console.error('login/verify error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/login/2fa ─────────────────────────────────
app.post('/api/login/2fa', auth, async (req, res) => {
    if (!client) return res.status(400).json({ error: 'Mulai login dulu.' });

    const { password } = req.body;
    try {
        await client.signInWithPassword(
            { apiId: loginState.apiId, apiHash: loginState.apiHash },
            { password: async () => password, onError: async () => true }
        );
        const str = client.session.save();
        saveSession(str);
        const me = await client.getMe();
        res.json({ status: 'success', user: { firstName: me.firstName, username: me.username } });
    } catch (e) {
        console.error('login/2fa error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/logout ─────────────────────────────────────
app.post('/api/logout', auth, async (req, res) => {
    try {
        if (client) await client.disconnect();
        client = null;
        if (fs.existsSync(SESSION_FILE)) fs.unlinkSync(SESSION_FILE);
        res.json({ status: 'ok' });
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/upload ─────────────────────────────────────
app.post('/api/upload', auth, upload.single('file'), async (req, res) => {
    if (!client || !await client.isUserAuthorized()) {
        return res.status(401).json({ error: 'Belum login ke Telegram.' });
    }

    const { chatId, caption } = req.body;
    const file = req.file;
    if (!file)   return res.status(400).json({ error: 'Tidak ada file.' });
    if (!chatId) return res.status(400).json({ error: 'chatId wajib.' });

    const origName = Buffer.from(file.originalname, 'latin1').toString('utf8');
    const tmpPath  = file.path;

    try {
        console.log(`⬆️  Upload: ${origName} (${fmtSize(file.size)}) → ${chatId}`);
        const entity = await client.getEntity(chatId);

        const result = await client.sendFile(entity, {
            file:         tmpPath,
            caption:      caption || `📁 ${origName}\n📦 ${fmtSize(file.size)}\n🕐 ${new Date().toLocaleString('id-ID')}`,
            forceDocument: true,
            workers:       4,
            progressCallback: (p) => process.stdout.write(`\r   ${Math.round(p * 100)}%  `),
        });

        console.log(`\n✅ Upload OK: msg_id=${result.id}`);
        fs.unlinkSync(tmpPath);

        res.json({ status: 'ok', messageId: result.id.toString() });
    } catch (e) {
        if (fs.existsSync(tmpPath)) fs.unlinkSync(tmpPath);
        console.error('\n❌ Upload error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/download ───────────────────────────────────
app.post('/api/download', auth, async (req, res) => {
    if (!client || !await client.isUserAuthorized()) {
        return res.status(401).json({ error: 'Belum login.' });
    }

    const { chatId, messageId } = req.body;
    if (!chatId || !messageId) return res.status(400).json({ error: 'chatId dan messageId wajib.' });

    try {
        const entity   = await client.getEntity(chatId);
        const messages = await client.getMessages(entity, { ids: [parseInt(messageId)] });

        if (!messages?.length || !messages[0]) {
            return res.status(404).json({ error: 'Pesan tidak ditemukan.' });
        }

        const msg = messages[0];
        if (!msg.media) return res.status(404).json({ error: 'Pesan tidak memiliki file.' });

        console.log(`⬇️  Download msg ${messageId} dari ${chatId}`);

        const buffer = await client.downloadMedia(msg, {
            progressCallback: (dl, total) => {
                if (total) process.stdout.write(`\r   ${Math.round(dl / total * 100)}%  `);
            }
        });

        console.log('\n✅ Download OK');
        res.send(Buffer.from(buffer));
    } catch (e) {
        console.error('\n❌ Download error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/note ───────────────────────────────────────
app.post('/api/note', auth, async (req, res) => {
    if (!client || !await client.isUserAuthorized()) {
        return res.status(401).json({ error: 'Belum login.' });
    }

    const { chatId, text, label } = req.body;
    if (!chatId || !text) return res.status(400).json({ error: 'chatId dan text wajib.' });

    try {
        const entity = await client.getEntity(chatId);
        const msg    = `📝 **${label || 'Catatan'}**\n\n\`\`\`\n${text}\n\`\`\`\n\n🕐 ${new Date().toLocaleString('id-ID')}`;
        const result = await client.sendMessage(entity, { message: msg });
        res.json({ status: 'ok', messageId: result.id.toString() });
    } catch (e) {
        console.error('note error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ── POST /api/delete ─────────────────────────────────────
app.post('/api/delete', auth, async (req, res) => {
    if (!client || !await client.isUserAuthorized()) {
        return res.status(401).json({ error: 'Belum login.' });
    }

    const { chatId, messageId } = req.body;
    try {
        const entity = await client.getEntity(chatId);
        await client.deleteMessages(entity, [parseInt(messageId)], { revoke: true });
        res.json({ status: 'ok' });
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

// ── Helper ───────────────────────────────────────────────
function fmtSize(b) {
    if (b < 1024)       return b + ' B';
    if (b < 1048576)    return (b / 1024).toFixed(1) + ' KB';
    if (b < 1073741824) return (b / 1048576).toFixed(1) + ' MB';
    return (b / 1073741824).toFixed(2) + ' GB';
}

// ── Start ────────────────────────────────────────────────
autoConnect().then(() => {
    app.listen(PORT, () => {
        console.log(`\n🚀 TeleStore Sidecar berjalan di http://localhost:${PORT}`);
        console.log(`🔐 Secret: ${SECRET ? '✅ Aktif' : '⚠️ Tidak ada (set SIDECAR_SECRET di .env)'}\n`);
    });
});
