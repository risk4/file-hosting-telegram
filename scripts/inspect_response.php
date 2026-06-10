<?php
$path = 'C:\\Users\\Kembar\\AppData\\Roaming\\Code\\User\\workspaceStorage\\ca34abe4195d37bc2fbc06a272b4dc0b\\GitHub.copilot-chat\\chat-session-resources\\b048b3ec-e34c-4cd0-b3ed-404c3db247dc\\call_twIAYMkSNZMfwwTx1YYvjhkI__vscode-1780813281042\\content.txt';
if(!file_exists($path)){
    echo "file not found: $path\n"; exit(1);
}
$json = file_get_contents($path);
$data = json_decode($json, true);
if(!$data){ echo "json decode failed\n"; exit(1); }
foreach($data as $i => $item){
    echo "--- ITEM $i URL: {$item['url']} STATUS: {$item['status']} ---\n";
    echo substr($item['body'],0,2000) . "\n\n";
}
