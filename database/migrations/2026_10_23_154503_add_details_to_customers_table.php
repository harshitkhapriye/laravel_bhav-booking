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
        Schema::table('customers', function (Blueprint $table) {
            // Drop unique constraint on mobile so family sub-profiles can share their parent's mobile
            $table->dropUnique(['mobile']);

            $table->enum('party_type', ['CUSTOMER', 'SUPPLIER'])->default('CUSTOMER')->after('name');
            $table->foreignId('parent_customer_id')->nullable()->after('party_type')->constrained('customers')->nullOnDelete();
            $table->string('email', 150)->nullable()->after('mobile');
            $table->text('address')->nullable()->after('email');
            $table->string('profile_image')->nullable()->after('address');
            $table->date('birthday')->nullable()->after('profile_image');
            $table->date('anniversary')->nullable()->after('birthday');
            $table->string('aadhaar_no', 12)->nullable()->after('anniversary');
            $table->string('pan_no', 10)->nullable()->after('aadhaar_no');
            $table->string('gst_no', 15)->nullable()->after('pan_no');
            $table->string('lic_no', 100)->nullable()->after('gst_no');
            $table->string('reg_no', 100)->nullable()->after('lic_no');
            $table->string('logo_title', 100)->nullable()->after('reg_no');
            $table->string('shop_name', 100)->nullable()->after('logo_title');
            $table->string('state', 60)->nullable()->after('shop_name');
            $table->string('district', 60)->nullable()->after('state');
            $table->string('area', 100)->nullable()->after('district');
            $table->text('shop_address')->nullable()->after('area');
            $table->string('shop_logo')->nullable()->after('shop_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['parent_customer_id']);
            $table->dropColumn([
                'party_type',
                'parent_customer_id',
                'email',
                'address',
                'profile_image',
                'birthday',
                'anniversary',
                'aadhaar_no',
                'pan_no',
                'gst_no',
                'lic_no',
                'reg_no',
                'logo_title',
                'shop_name',
                'state',
                'district',
                'area',
                'shop_address',
                'shop_logo',
            ]);
            $table->unique('mobile');
        });
    }
};
