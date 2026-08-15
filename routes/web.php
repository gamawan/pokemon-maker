<?php
	use App\Http\Controllers\PokemonController;
	use App\Http\Controllers\TypeEffectivenessController;
	use Illuminate\Support\Facades\Route;
	use App\Http\Controllers\TypeController;
	
	Route::get('/', [PokemonController::class, 'index'])->name('pokemon.index');
	Route::get('/pokemon/export', [PokemonController::class, 'export'])->name('pokemon.export');
	Route::post('/pokemon/import', [PokemonController::class, 'import'])->name('pokemon.import');
	Route::delete('/pokemon/clear-all', [PokemonController::class, 'clearAll'])->name('pokemon.clearAll');
	
	// Reorder route MUST be defined before or alongside standard resource routes
	Route::post('/pokemon/reorder', [PokemonController::class, 'reorder'])->name('pokemon.reorder');
	
	Route::post('/pokemon', [PokemonController::class, 'store'])->name('pokemon.store');
	Route::put('/pokemon/{pokemon}', [PokemonController::class, 'update'])->name('pokemon.update');
	Route::delete('/pokemon/{pokemon}', [PokemonController::class, 'destroy'])->name('pokemon.destroy');
	
	// Dynamic Types Routes
	Route::post('/types', [TypeController::class, 'store'])->name('types.store');
	Route::put('/types/{type}', [TypeController::class, 'update'])->name('types.update');
	Route::delete('/types/{type}', [TypeController::class, 'destroy'])->name('types.destroy');
	
	Route::post('/type-effectiveness/update', [TypeEffectivenessController::class, 'update'])->name('type-effectiveness.update');
	Route::post('/type-effectiveness/reset', [TypeEffectivenessController::class, 'reset'])->name('type-effectiveness.reset');
	
	Route::delete('/pokemon/clear-all', [PokemonController::class, 'clearAll'])->name('pokemon.clear-all');
	Route::post('/pokemon/move-slot', [PokemonController::class, 'moveSlot'])->name('pokemon.moveSlot');
	
