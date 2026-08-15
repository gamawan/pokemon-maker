<?php
	
	namespace Database\Seeders;
	
	use App\Models\Type;
	use Illuminate\Database\Seeder;
	
	class TypeSeeder extends Seeder
	{
		public function run(): void
		{
			$defaultTypes = [
            'divine'  => '#f59e0b',
            'magic'   => '#a855f7',
            'cosmic'  => '#6366f1',
            'machine' => '#64748b',
            'vital'   => '#f43f5e',
            'spirit'  => '#8b5cf6',
            'sea'     => '#3b82f6',
            'sky'     => '#0ea5e9',
            'soil'    => '#a16207',
            'flora'   => '#10b981',
            'flame'   => '#f97316',
            'frost'   => '#06b6d4',
			];
			
			foreach ($defaultTypes as $name => $color) {
				Type::firstOrCreate(
                ['name' => $name],
                ['color_hex' => $color]
				);
			}
		}
	}
