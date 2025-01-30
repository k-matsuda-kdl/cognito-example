<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');  // passwordカラムの削除
            $table->string('sub')->unique()->nullable(false);  // subカラムの追加
            $table->string('name')->nullable()->change();  // nameカラムのNOT NULL制約の削除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password');  // passwordカラムの再追加
            $table->dropColumn('sub');  // subカラムの削除
            $table->string('name')->nullable(false)->change();  // nameカラムのNOT NULL制約の再設定
        });
    }
};
