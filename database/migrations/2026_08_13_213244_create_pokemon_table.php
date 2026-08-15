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
			Schema::create('pokemon', function (Blueprint $table) {
				$table->id();
				$table->string('name');
				$table->string('species')->nullable();       // Main species (e.g. komodo, tapir)
				$table->integer('evo_number')->nullable();   // Evolution stage number (e.g. 1, 2, 3)
				$table->string('image_path')->nullable();   // Image location/path or URL
				$table->text('insp')->nullable();
				$table->string('type1');
				$table->string('type2')->nullable();
				$table->text('description')->nullable();
				$table->timestamps();
			});
		}
		
		/**
			* Reverse the migrations.
		*/
		public function down(): void
		{
			Schema::dropIfExists('pokemon');
		}
	};
