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
			Schema::create('types', function (Blueprint $table) {
				$table->id();
				$table->string('name')->unique();     // e.g., 'flame', 'water'
				$table->string('color_hex')->default('#64748b'); // HEX color for badges and charts
				$table->timestamps();
			});
		}
		
		/**
			* Reverse the migrations.
		*/
		public function down(): void
		{
			Schema::dropIfExists('types');
		}
	};
