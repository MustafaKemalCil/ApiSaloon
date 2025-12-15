<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // android / ios
            $table->string('version'); // 2.0, 2.1 vb
            $table->string('file_path'); // APK/IPA yolu
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('app_versions');
    }
};
