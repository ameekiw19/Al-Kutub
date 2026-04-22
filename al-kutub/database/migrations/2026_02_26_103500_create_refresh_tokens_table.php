<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usersIdType = $this->resolveUsersIdType();

        if (!Schema::hasTable('refresh_tokens')) {
            Schema::create('refresh_tokens', function (Blueprint $table) use ($usersIdType) {
                $table->id();
                // Keep FK type compatible with legacy users.id type in this project.
                if (str_contains($usersIdType, 'bigint')) {
                    if (str_contains($usersIdType, 'unsigned')) {
                        $table->unsignedBigInteger('user_id');
                    } else {
                        $table->bigInteger('user_id');
                    }
                } else {
                    if (str_contains($usersIdType, 'unsigned')) {
                        $table->unsignedInteger('user_id');
                    } else {
                        $table->integer('user_id');
                    }
                }
                $table->string('token_hash', 128);
                $table->timestamp('expires_at');
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        // Migration can be retried after partial failure; ensure missing indexes/FK are added.
        if (!$this->hasIndex('refresh_tokens', 'refresh_tokens_token_hash_unique')) {
            DB::statement('ALTER TABLE refresh_tokens ADD UNIQUE refresh_tokens_token_hash_unique (token_hash)');
        }

        if (!$this->hasIndex('refresh_tokens', 'refresh_tokens_user_id_expires_at_index')) {
            DB::statement('ALTER TABLE refresh_tokens ADD INDEX refresh_tokens_user_id_expires_at_index (user_id, expires_at)');
        }

        $refreshUserIdType = $this->resolveRefreshUserIdType();
        if ($refreshUserIdType !== $usersIdType) {
            DB::statement("ALTER TABLE refresh_tokens MODIFY user_id {$usersIdType} NOT NULL");
        }

        if (!$this->hasForeignKey('refresh_tokens', 'refresh_tokens_user_id_foreign')) {
            DB::statement(
                'ALTER TABLE refresh_tokens ADD CONSTRAINT refresh_tokens_user_id_foreign ' .
                'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }

    private function resolveUsersIdType(): string
    {
        $usersIdColumn = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'id'");
        return strtolower((string) ($usersIdColumn->Type ?? 'int(11)'));
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::selectOne(
            "SELECT COUNT(*) AS total FROM information_schema.STATISTICS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $indexName]
        );

        return ((int) ($result->total ?? 0)) > 0;
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        $result = DB::selectOne(
            "SELECT COUNT(*) AS total FROM information_schema.TABLE_CONSTRAINTS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, $constraintName]
        );

        return ((int) ($result->total ?? 0)) > 0;
    }

    private function resolveRefreshUserIdType(): string
    {
        $refreshUserIdColumn = DB::selectOne("SHOW COLUMNS FROM refresh_tokens WHERE Field = 'user_id'");
        return strtolower((string) ($refreshUserIdColumn->Type ?? ''));
    }
};
