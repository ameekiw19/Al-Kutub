<?php

namespace Tests\Feature\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ApiSchemaSetup
{
    protected function useSqliteMemoryOrSkip(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    protected function resetTables(array $tables): void
    {
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    protected function createUsersTable(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->text('deskripsi')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    protected function createKitabTable(): void
    {
        Schema::create('kitab', function (Blueprint $table) {
            $table->increments('id_kitab');
            $table->string('judul');
            $table->string('penulis');
            $table->text('deskripsi');
            $table->string('kategori');
            $table->string('bahasa')->nullable();
            $table->string('file_pdf')->nullable();
            $table->string('cover')->nullable();
            $table->integer('views')->default(0);
            $table->integer('downloads')->default(0);
            $table->json('viewed_by')->nullable();
            $table->string('publication_status')->default('draft');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->string('status_note')->nullable();
            $table->timestamps();
        });
    }

    protected function createKitabRevisionsTable(): void
    {
        Schema::create('kitab_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('kitab_id');
            $table->unsignedInteger('version_no');
            $table->string('action', 60);
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->json('changed_fields')->nullable();
            $table->string('old_file_pdf')->nullable();
            $table->string('old_cover')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    protected function createAuditLogsTable(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function createNotificationTables(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->string('action_url')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'notification_id']);
        });
    }

    protected function createFcmTokensTable(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_token');
            $table->string('device_type')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function createReadingNotesTable(): void
    {
        Schema::create('reading_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('kitab_id');
            $table->text('note_content');
            $table->integer('page_number')->nullable();
            $table->text('highlighted_text')->nullable();
            $table->string('note_color')->default('#FFFF00');
            $table->boolean('is_private')->default(true);
            $table->string('client_request_id', 64)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'client_request_id']);
        });
    }

    protected function createKitabTranscriptSegmentsTable(): void
    {
        Schema::create('kitab_transcript_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('kitab_id');
            $table->string('section_key', 120)->nullable();
            $table->string('transcript_type', 40)->default('page');
            $table->string('title')->nullable();
            $table->text('content');
            $table->text('content_translation')->nullable();
            $table->text('content_arabic')->nullable();
            $table->string('language', 32)->nullable();
            $table->unsignedInteger('page_start')->nullable();
            $table->unsignedInteger('page_end')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
}
