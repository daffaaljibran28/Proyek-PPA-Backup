<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addVideoUrl = ! Schema::hasColumn('informasis', 'video_url');
        $addVideoPath = ! Schema::hasColumn('informasis', 'video_path');

        if (! $addVideoUrl && ! $addVideoPath) {
            return;
        }

        Schema::table('informasis', function (Blueprint $table) use ($addVideoUrl, $addVideoPath) {
            if ($addVideoUrl) {
                $table->string('video_url')->nullable();
            }
            if ($addVideoPath) {
                $table->string('video_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        $dropColumns = array_values(array_filter(
            ['video_url', 'video_path'],
            fn (string $column) => Schema::hasColumn('informasis', $column)
        ));

        if ($dropColumns === []) {
            return;
        }

        Schema::table('informasis', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }
};
