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
			Schema::create('type_effectivenesses', function (Blueprint $table) {
				$table->id();
				$table->string('attacker_type');
				$table->string('defender_type');
				$table->float('multiplier')->default(1.0); // 2.0 (Super), 1.0 (Normal), 0.5 (Not Very), 0.0 (Immune)
				$table->timestamps();
				
				$table->unique(['attacker_type', 'defender_type']);
			});
		}
		
		/**
			* Reverse the migrations.
		*/
		public function down(): void
		{
			Schema::dropIfExists('type_effectivenesses');
		}
	};
