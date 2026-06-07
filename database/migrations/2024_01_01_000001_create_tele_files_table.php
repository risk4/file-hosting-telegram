<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tele_files', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');                     // nama asli file
            $table->string('original_name');            // nama saat upload
            $table->string('type');                     // image, video, doc, note, other
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->default(0);     // bytes
            $table->string('category')->default('uncategorized');
            $table->string('telegram_message_id');
            $table->string('telegram_chat_id');
            $table->text('description')->nullable();
            $table->text('content')->nullable();        // untuk note
            $table->boolean('is_public')->default(true);
            $table->string('uploaded_by')->default('guest'); // 'guest' atau username admin
            $table->integer('download_count')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_public']);
            $table->index('category');
            $table->index('created_at');
        });

        Schema::create('tele_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->default('📁');
            $table->string('color')->default('#6b7280');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tele_files');
        Schema::dropIfExists('tele_categories');
    }
};
