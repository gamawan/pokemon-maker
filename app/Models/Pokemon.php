<?php
	
	namespace App\Models;
	
	use Illuminate\Database\Eloquent\Model;
	
	class Pokemon extends Model
	{
		protected $fillable = [
        'name',
        'species',
        'evo_number',
        'image_path',
        'insp',
        'type1',
        'type2',
        'description',
		'slot',
		];
	}
