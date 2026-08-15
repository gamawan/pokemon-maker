<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<!-- Favicon -->
		<link rel="icon" type="image/png" href="{{ asset('images/placeholder.png') }}">
		<title>Custom Pokémon Maker</title>
		
		<!-- Alpine x-cloak Style (Prevents FOUC flicker on reload) -->
		<style>
			[x-cloak] { display: none !important; }
			
			/* Custom Floating Styling during Drag */
			.sortable-drag-active {
			opacity: 0.95 !important;
			cursor: grabbing !important;
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75) !important;
			z-index: 9999 !important;
			transform: scale(1.02);
			}
		</style>
		
		<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
		
		<!-- Tailwind CSS -->
		<script src="https://cdn.tailwindcss.com"></script>
		<!-- Alpine.js -->
		<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
		<!-- Chart.js -->
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	</head>
	<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased text-xs" x-data="pokemonApp">
		
		@php
        // Type Badge Styling & Color Mapping
        $types = [
		'divine'  => ['class' => 'bg-amber-500/20 text-amber-300 border-amber-500/40', 'hex' => '#f59e0b'],
		'magic'   => ['class' => 'bg-purple-500/20 text-purple-300 border-purple-500/40', 'hex' => '#a855f7'],
		'cosmic'  => ['class' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40', 'hex' => '#6366f1'],
		'machine' => ['class' => 'bg-slate-500/20 text-slate-300 border-slate-500/40', 'hex' => '#64748b'],
		'vital'   => ['class' => 'bg-rose-500/20 text-rose-300 border-rose-500/40', 'hex' => '#f43f5e'],
		'spirit'  => ['class' => 'bg-violet-500/20 text-violet-300 border-violet-500/40', 'hex' => '#8b5cf6'],
		'sea'     => ['class' => 'bg-blue-500/20 text-blue-300 border-blue-500/40', 'hex' => '#3b82f6'],
		'sky'     => ['class' => 'bg-sky-500/20 text-sky-300 border-sky-500/40', 'hex' => '#0ea5e9'],
		'soil'    => ['class' => 'bg-yellow-700/20 text-yellow-400 border-yellow-700/40', 'hex' => '#a16207'],
		'flora'   => ['class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40', 'hex' => '#10b981'],
		'flame'   => ['class' => 'bg-orange-500/20 text-orange-300 border-orange-500/40', 'hex' => '#f97316'],
		'frost'   => ['class' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40', 'hex' => '#06b6d4'],
        ];
		
        function sortLink($col, $currentSortBy, $currentSortDir) {
		$dir = ($currentSortBy === $col && $currentSortDir === 'asc') ? 'desc' : 'asc';
		return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir]);
        }
		@endphp
		
		<!-- STICKY APPLICATION HEADER -->
		<header class="sticky top-0 z-30 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 shadow-md">
			<div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex flex-col sm:flex-row items-center justify-between gap-3">
				
				<!-- App Brand / Logo -->
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center shadow-inner shrink-0">
						<img src="{{ asset('images/placeholder.png') }}" class="w-5 h-5 object-contain" alt="Logo">
					</div>
					<div>
						<h1 class="text-sm font-bold text-white tracking-wide flex items-center gap-2">
							Pokémon Maker
							<span class="text-[9px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-1.5 py-0.2 rounded-full">v1.2</span>
						</h1>
						<p class="text-[10px] text-slate-400">Custom Registry & Dynamic Analytics Studio</p>
					</div>
				</div>
				
				<!-- Global Action Buttons -->
				<div class="flex items-center gap-2 flex-wrap">
					<button @click="openTypeModal = true" type="button" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer shadow-sm">
						<svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
						Manage Types
					</button>
					
					<a href="{{ route('pokemon.export') }}" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm">
						<svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
						Export
					</a>
					
					<button @click="openImportModal = true" type="button" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer shadow-sm">
						<svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
						Import
					</button>
					
					<!-- CLEAR ALL DATA BUTTON -->
					<form action="{{ route('pokemon.clear-all') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete ALL Pokémon entries from the database? This cannot be undone!');" class="inline-block">
						@csrf
						@method('DELETE')
						<button type="submit" class="inline-flex items-center gap-1.5 bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 border border-rose-700/80 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer shadow-sm">
							<svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
							Clear All Data
						</button>
					</form>
					
					<button @click="openAddModal = true" type="button" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-md shadow-indigo-600/20 cursor-pointer">
						<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
						Add Pokémon
					</button>
				</div>
				
			</div>
		</header>
		
		<!-- MAIN APP CONTENT CONTAINER -->
		<div class="max-w-7xl mx-auto px-3 sm:px-4 py-4 space-y-4">
			
			<!-- Success Notification -->
			@if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-600 text-emerald-300 px-3 py-1.5 rounded-lg flex items-center justify-between text-xs">
                <span class="font-medium">✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white text-sm">&times;</button>
			</div>
			@endif
			
			<!-- SECTION 1: COMBINED REGISTRY ANALYTICS DASHBOARD & DUAL MATRICES (COLLAPSIBLE DROPDOWN) -->
			<div x-data="{ openAnalytics: true }" class="bg-slate-800/80 border border-slate-700/80 rounded-xl shadow-lg overflow-hidden">
				
				<!-- Accordion Header Bar -->
				<button @click="openAnalytics = !openAnalytics" 
				type="button" 
				class="w-full bg-slate-900/90 hover:bg-slate-900 px-4 py-2.5 flex items-center justify-between border-b border-slate-700/60 transition cursor-pointer select-none">
					<div class="flex items-center gap-2">
						<span class="text-indigo-400 text-sm">📊</span>
						<h2 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Analytics & Type Effectiveness Studio</h2>
						<span class="text-[10px] text-slate-400 font-mono">({{ $totalCount }} Total Entries)</span>
					</div>
					<div class="flex items-center gap-2 text-slate-400 text-[11px] font-semibold">
						<span x-text="openAnalytics ? 'Hide Panel' : 'Show Panel'"></span>
						<svg class="w-4 h-4 transform transition-transform duration-200" :class="openAnalytics ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
						</svg>
					</div>
				</button>
				
				<!-- Collapsible Content Body -->
				<div x-show="openAnalytics" x-collapse class="p-3 space-y-3">
					
					<!-- Top Row: Metrics & Charts -->
					<div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-stretch">
						
						<!-- Left: Summary Stats & Type Composition Doughnut -->
						<div class="lg:col-span-4 bg-slate-900/90 border border-slate-700/70 rounded-lg p-2.5 flex flex-col justify-between space-y-2">
							<div class="grid grid-cols-4 gap-1.5 text-center">
								<div class="bg-slate-800/90 border border-slate-700/60 p-1.5 rounded">
									<span class="text-[9px] text-slate-400 block uppercase font-bold">Total</span>
									<span class="text-xs font-mono font-bold text-white">{{ $totalCount }}</span>
								</div>
								<div class="bg-slate-800/90 border border-slate-700/60 p-1.5 rounded">
									<span class="text-[9px] text-slate-400 block uppercase font-bold">Species</span>
									<span class="text-xs font-mono font-bold text-indigo-400">{{ $uniqueSpeciesCount }}</span>
								</div>
								<div class="bg-slate-800/90 border border-slate-700/60 p-1.5 rounded">
									<span class="text-[9px] text-slate-400 block uppercase font-bold">Single</span>
									<span class="text-xs font-mono font-bold text-sky-400">{{ $singleTypeCount }}</span>
								</div>
								<div class="bg-slate-800/90 border border-slate-700/60 p-1.5 rounded">
									<span class="text-[9px] text-slate-400 block uppercase font-bold">Dual</span>
									<span class="text-xs font-mono font-bold text-purple-400">{{ $dualTypeCount }}</span>
								</div>
							</div>
							
							<div class="relative h-28 flex items-center justify-center pt-1">
								<canvas id="typeCompositionChart"></canvas>
							</div>
						</div>
						
						<!-- Right: Element Frequency Bar Chart -->
						<div class="lg:col-span-8 bg-slate-900/90 border border-slate-700/70 rounded-lg p-2.5 flex flex-col justify-between">
							<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
								📊 Element Breakdown Frequency
							</span>
							<div class="h-32 w-full">
								<canvas id="typesBarChart"></canvas>
							</div>
						</div>
						
					</div>
					
					<!-- Bottom Row: Side-By-Side Matrices -->
					@php $typeMap = $dbTypes->keyBy('name'); @endphp
					<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 pt-3 border-t border-slate-700/60">
						
						<!-- MATRIX 1: SYMMETRIC DUAL TYPING MAP -->
						<div class="bg-slate-900/90 border border-slate-700/70 rounded-xl p-3 space-y-2 flex flex-col justify-between shadow-lg">
							<div class="flex items-center justify-between text-[11px]">
								<span class="font-bold text-slate-200 uppercase tracking-wider">
									🧩 Symmetric Dual Typing Matrix Map
								</span>
								<span class="text-slate-400 text-[10px]">
									<span class="bg-sky-600/80 text-white px-1.5 py-0.5 rounded font-bold">Blue</span> Pure | 
									<span class="bg-indigo-600/80 text-white px-1.5 py-0.5 rounded font-bold">Indigo</span> Dual | 
									<span class="text-slate-500 font-bold">(-)</span> Uncreated
								</span>
							</div>
							
							<div class="overflow-x-auto py-1">
								<table class="text-center border-collapse mx-auto">
									<thead>
										<tr>
											<th class="w-20 h-8 sm:h-9 p-0.5 text-[9px] font-bold text-slate-500 border-b border-r border-slate-800 bg-slate-950/80 align-middle">
												T1 \ T2
											</th>
											@foreach($dbTypes as $colType)
                                            <th class="w-8 sm:w-9 h-8 sm:h-9 p-0 border-b border-slate-800 bg-slate-950/50 align-middle">
                                                <span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center rounded text-[8px] sm:text-[9px] font-bold uppercase border mx-auto"
												style="background-color: {{ $colType->color_hex }}20; border-color: {{ $colType->color_hex }}50; color: {{ $colType->color_hex }};">
                                                    {{ substr($colType->name, 0, 3) }}
												</span>
											</th>
											@endforeach
										</tr>
									</thead>
									<tbody class="divide-y divide-slate-800/60 text-[10px] sm:text-xs font-mono">
										@foreach($dbTypes as $rowType)
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="w-20 h-8 sm:h-9 px-1.5 font-bold border-r border-slate-800 bg-slate-950/50 text-left whitespace-nowrap align-middle">
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-bold uppercase border"
												style="background-color: {{ $rowType->color_hex }}20; border-color: {{ $rowType->color_hex }}50; color: {{ $rowType->color_hex }};">
                                                    {{ $rowType->name }}
												</span>
											</td>
											
                                            @foreach($dbTypes as $colType)
											@php
											$rName = $rowType->name;
											$cName = $colType->name;
											$count = $typeMatrix[$rName][$cName] ?? 0;
											$isDiagonal = ($rName === $cName);
											@endphp
											
											<td class="w-8 sm:w-9 h-8 sm:h-9 p-0 align-middle text-center border-r border-slate-800/40 last:border-r-0 {{ $isDiagonal ? 'bg-sky-950/30' : '' }}">
												@if($isDiagonal)
												@if($count > 0)
												<span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center bg-sky-600 text-white font-bold rounded-md text-[10px] sm:text-xs shadow-sm mx-auto">{{ $count }}</span>
												@else
												<span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center text-slate-600 font-semibold mx-auto">-</span>
												@endif
												@elseif($count > 0)
												<span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center bg-indigo-600 text-white font-bold rounded-md text-[10px] sm:text-xs shadow-sm mx-auto">{{ $count }}</span>
												@else
												<span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center text-slate-600 font-semibold mx-auto">-</span>
												@endif
											</td>
                                            @endforeach
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						
						<!-- MATRIX 2: INTERACTIVE TYPE EFFECTIVENESS EDITOR -->
						<div class="bg-slate-900/90 border border-slate-700/70 rounded-xl p-3 space-y-2 flex flex-col justify-between shadow-lg">
							<div class="flex items-center justify-between text-[11px]">
								<span class="font-bold text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
									⚡ Type Effectiveness Editor
									<span class="text-[9px] text-indigo-400 font-normal">(Click cell to cycle)</span>
								</span>
								
								<div class="flex items-center gap-2">
									<span class="text-slate-400 text-[10px] flex items-center gap-1">
										<span class="bg-emerald-600 text-white px-1.5 py-0.5 rounded font-bold">2×</span>
										<span class="bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded">1×</span>
										<span class="bg-amber-600 text-white px-1.5 py-0.5 rounded font-bold">½×</span>
										<span class="bg-rose-950 text-rose-300 border border-rose-800 px-1.5 py-0.5 rounded font-bold">0×</span>
									</span>
									
									<button type="button" @click="resetEffectiveness()" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded text-[10px] font-semibold transition cursor-pointer">
										Reset All (1×)
									</button>
								</div>
							</div>
							
							<div class="overflow-x-auto py-1">
								<table class="text-center border-collapse mx-auto">
									<thead>
										<tr>
											<th class="w-20 h-8 sm:h-9 p-0.5 text-[9px] font-bold text-slate-500 border-b border-r border-slate-800 bg-slate-950/80 align-middle">
												Atk \ Def
											</th>
											@foreach($dbTypes as $colType)
                                            <th class="w-8 sm:w-9 h-8 sm:h-9 p-0 border-b border-slate-800 bg-slate-950/50 align-middle">
                                                <span class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center rounded text-[8px] sm:text-[9px] font-bold uppercase border mx-auto"
												style="background-color: {{ $colType->color_hex }}20; border-color: {{ $colType->color_hex }}50; color: {{ $colType->color_hex }};">
                                                    {{ substr($colType->name, 0, 3) }}
												</span>
											</th>
											@endforeach
										</tr>
									</thead>
									<tbody class="divide-y divide-slate-800/60 text-[10px] sm:text-xs font-mono">
										@foreach($dbTypes as $rowType)
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="w-20 h-8 sm:h-9 px-1.5 font-bold border-r border-slate-800 bg-slate-950/50 text-left whitespace-nowrap align-middle">
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-bold uppercase border"
												style="background-color: {{ $rowType->color_hex }}20; border-color: {{ $rowType->color_hex }}50; color: {{ $rowType->color_hex }};">
                                                    {{ $rowType->name }}
												</span>
											</td>
											
                                            @foreach($dbTypes as $colType)
											@php
											$att = $rowType->name;
											$def = $colType->name;
											@endphp
											
											<td class="w-8 sm:w-9 h-8 sm:h-9 p-0 align-middle text-center border-r border-slate-800/40 last:border-r-0">
												<button type="button" @click="cycleEffectiveness('{{ $att }}', '{{ $def }}')" class="w-7 sm:w-8 h-7 sm:h-8 inline-flex items-center justify-center rounded-md text-[10px] sm:text-xs font-bold transition cursor-pointer mx-auto active:scale-95"
												:class="{
												'bg-emerald-600 text-white shadow-sm': effectiveness['{{ $att }}']['{{ $def }}'] === 2.0,
												'text-slate-600 hover:text-white hover:bg-slate-800': effectiveness['{{ $att }}']['{{ $def }}'] === 1.0,
												'bg-amber-600 text-white shadow-sm': effectiveness['{{ $att }}']['{{ $def }}'] === 0.5,
												'bg-rose-950 text-rose-300 border border-rose-800 shadow-sm': effectiveness['{{ $att }}']['{{ $def }}'] === 0.0
												}"
												:title="`Attacker: {{ ucfirst($att) }} vs Defender: {{ ucfirst($def) }} (${effectiveness['{{ $att }}']['{{ $def }}']}x)`">
													<template x-if="effectiveness['{{ $att }}']['{{ $def }}'] === 2.0"><span>2</span></template>
													<template x-if="effectiveness['{{ $att }}']['{{ $def }}'] === 1.0"><span>1</span></template>
													<template x-if="effectiveness['{{ $att }}']['{{ $def }}'] === 0.5"><span>½</span></template>
													<template x-if="effectiveness['{{ $att }}']['{{ $def }}'] === 0.0"><span>0</span></template>
												</button>
											</td>
                                            @endforeach
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			
			<!-- SECTION 2: MAIN REGISTRY DISPLAY CONTAINER (COLLAPSIBLE DROPDOWN) -->
			<div x-data="{ openRegistry: true }" class="bg-slate-800/60 border border-slate-700/80 rounded-xl shadow-xl relative z-20 backdrop-blur-md overflow-hidden">
				
				<!-- Accordion Header Bar -->
				<button @click="openRegistry = !openRegistry" 
				type="button" 
				class="w-full bg-slate-900/90 hover:bg-slate-900 px-4 py-2.5 flex items-center justify-between border-b border-slate-800 transition cursor-pointer select-none">
					<div class="flex items-center gap-2">
						<span class="text-indigo-400 text-sm">📋</span>
						<h2 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Pokémon Registry List</h2>
						<span class="text-[10px] text-slate-400 font-mono">(Showing {{ $pokemons->firstItem() ?? 0 }}-{{ $pokemons->lastItem() ?? 0 }} of {{ $pokemons->total() }})</span>
					</div>
					<div class="flex items-center gap-2 text-slate-400 text-[11px] font-semibold">
						<span x-text="openRegistry ? 'Hide Registry' : 'Show Registry'"></span>
						<svg class="w-4 h-4 transform transition-transform duration-200" :class="openRegistry ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
						</svg>
					</div>
				</button>
				
				<!-- Collapsible Registry Body -->
				<div x-show="openRegistry" x-collapse>
					
					<!-- FILTER & DATA CONTROL TOOLBAR STRIP -->
					<div class="bg-slate-900/90 border-b border-slate-800 py-1.5 px-3 sm:px-4">
						<div class="max-w-7xl mx-auto flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2">
							
							<form action="{{ route('pokemon.index') }}" method="GET" class="flex items-center gap-1.5 flex-wrap flex-1 text-[11px]">
								<div class="relative flex-1 sm:flex-none w-full sm:w-40">
									<div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-slate-500">
										<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
									</div>
									<input type="text" name="search" value="{{ $searchQuery }}" placeholder="Search name..." class="w-full bg-slate-950 border border-slate-800 rounded-md pl-7 pr-2 py-1 text-slate-200 text-[11px] focus:outline-none focus:border-indigo-500 placeholder-slate-500 transition">
								</div>
								
								<!-- Multi-Type Checkbox Dropdown Filter -->
								<div x-data="{ openTypeFilter: false }" class="relative z-30">
									<button @click="openTypeFilter = !openTypeFilter" @click.outside="openTypeFilter = false" type="button" class="bg-slate-950 border border-slate-800 text-slate-300 hover:text-white text-[11px] rounded-md px-2 py-1 inline-flex items-center gap-1 focus:outline-none transition cursor-pointer">
										<span>Types</span>
										@if(count($filterTypes) > 0)
										<span class="bg-indigo-600 text-white font-mono text-[9px] font-bold px-1 rounded-full">
											{{ count($filterTypes) }}
										</span>
										@endif
										<svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
									</button>
									
									<div x-cloak x-show="openTypeFilter" x-transition class="absolute left-0 top-full mt-1 z-50 bg-slate-900 border border-slate-700/90 rounded-xl p-2.5 shadow-2xl w-60 space-y-2">
										<div class="flex items-center justify-between border-b border-slate-800 pb-1">
											<span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Select Elements</span>
											<button type="submit" class="text-[10px] font-semibold text-indigo-400 hover:text-indigo-300 cursor-pointer">Apply</button>
										</div>
										
										<div class="grid grid-cols-2 gap-1 max-h-48 overflow-y-auto pr-1">
											@foreach($dbTypes as $type)
											@php $isChecked = in_array(strtolower($type->name), $filterTypes); @endphp
											<label class="flex items-center gap-1.5 p-1 rounded bg-slate-950/60 border border-slate-800 hover:border-slate-700 cursor-pointer text-[10px] font-semibold select-none">
												<input type="checkbox" name="types[]" value="{{ $type->name }}" {{ $isChecked ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/50 cursor-pointer">
												<span class="truncate capitalize" style="color: {{ $type->color_hex }};">
													{{ $type->name }}
												</span>
											</label>
											@endforeach
										</div>
										
										<div class="pt-1 border-t border-slate-800 flex justify-between items-center text-[10px]">
											<a href="{{ route('pokemon.index') }}" class="text-slate-400 hover:text-slate-200">Clear All</a>
											<button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-2 py-0.5 rounded shadow">Apply</button>
										</div>
									</div>
								</div>
								
								<select name="sort" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 text-slate-300 text-[11px] rounded-md px-2 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
									<option value="slot" {{ $sortBy === 'slot' ? 'selected' : '' }}>Sort: Slot / Custom</option>
									<option value="id" {{ $sortBy === 'id' ? 'selected' : '' }}>Sort: ID</option>
									<option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Sort: Name</option>
									<option value="species" {{ $sortBy === 'species' ? 'selected' : '' }}>Sort: Species</option>
									<option value="evo_number" {{ $sortBy === 'evo_number' ? 'selected' : '' }}>Sort: Evo #</option>
									<option value="type1" {{ $sortBy === 'type1' ? 'selected' : '' }}>Sort: Type 1</option>
									<option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Sort: Date</option>
								</select>
								
								<select name="direction" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 text-slate-300 text-[11px] rounded-md px-2 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
									<option value="asc" {{ $sortDir === 'asc' ? 'selected' : '' }}>Asc (A-Z)</option>
									<option value="desc" {{ $sortDir === 'desc' ? 'selected' : '' }}>Desc (Z-A)</option>
								</select>
								
								<button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-medium transition cursor-pointer">
									Filter
								</button>
								
								@if(!empty($filterTypes) || !empty($searchQuery))
								<a href="{{ route('pokemon.index') }}" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-2 py-1 rounded-md text-[11px] font-semibold transition">
									Reset
								</a>
								@endif
							</form>
							
							<div class="flex items-center justify-between md:justify-end gap-2 border-t md:border-t-0 border-slate-800/80 pt-1.5 md:pt-0">
								<span class="text-[10px] text-slate-400 font-mono">
									<strong class="text-slate-200">{{ $pokemons->firstItem() ?? 0 }}-{{ $pokemons->lastItem() ?? 0 }}</strong> / <strong class="text-slate-200">{{ $pokemons->total() }}</strong>
								</span>
								
								<div class="inline-flex items-center p-0.5 bg-slate-950 rounded-md border border-slate-800">
									<button type="button" @click="setViewMode('table')" :class="viewMode === 'table' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded text-[10px] font-medium transition-all duration-150 cursor-pointer flex items-center gap-1">
										<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
										Table
									</button>
									<button type="button" @click="setViewMode('grid')" :class="viewMode === 'grid' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded text-[10px] font-medium transition-all duration-150 cursor-pointer flex items-center gap-1">
										<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
										Grid
									</button>
								</div>
							</div>
							
						</div>
					</div>
					
<!-- TABLE VIEW MODE (COMPACT + FULL DESCRIPTION) -->
<div x-cloak x-show="viewMode === 'table'" class="overflow-x-auto">
	<table class="w-full text-left border-collapse">
		<thead>
			<tr class="bg-slate-900/90 text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-700/80">
				<!-- 1. Drag Handle -->
				<th class="py-1 px-1 text-center w-5"></th>
				
				<!-- 2. Slot Number Jumper -->
				<th class="py-1 px-1.5 text-center w-14">
					<a href="{{ sortLink('slot', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						SLOT {!! $sortBy === 'slot' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>

				<!-- 3. Database Raw ID -->
				<th class="py-1 px-1.5 text-center w-10">
					<a href="{{ sortLink('id', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						ID {!! $sortBy === 'id' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>

				<th class="py-1 px-1.5 text-center w-10">Image</th>
				<th class="py-1 px-2">
					<a href="{{ sortLink('name', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						Name {!! $sortBy === 'name' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>
				<th class="py-1 px-2">
					<a href="{{ sortLink('species', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						Species {!! $sortBy === 'species' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>
				<th class="py-1 px-1.5 text-center">
					<a href="{{ sortLink('evo_number', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						Evo # {!! $sortBy === 'evo_number' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>
				<th class="py-1 px-2">Inspiration</th>
				<th class="py-1 px-1.5">
					<a href="{{ sortLink('type1', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						Type 1 {!! $sortBy === 'type1' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>
				<th class="py-1 px-1.5">
					<a href="{{ sortLink('type2', $sortBy, $sortDir) }}" class="hover:text-white inline-flex items-center gap-0.5">
						Type 2 {!! $sortBy === 'type2' ? ($sortDir === 'asc' ? '▲' : '▼') : '' !!}
					</a>
				</th>
				<!-- Description Header: Allowed to expand dynamically -->
				<th class="py-1 px-2.5">Description</th>
				<th class="py-1 px-2 text-center w-20">Actions</th>
			</tr>
		</thead>
		<tbody x-init="initSortable($el)" class="divide-y divide-slate-800/60 text-[11px]">
			@forelse($pokemons as $pokemon)
			<tr data-id="{{ $pokemon->id }}" class="hover:bg-slate-700/20 transition-colors duration-150">
				
				<!-- 1. Drag Handle -->
				<td class="py-1 px-0.5 text-center align-middle">
					<span class="drag-handle text-slate-500 hover:text-slate-200 cursor-grab active:cursor-grabbing select-none text-xs px-1">
						⋮⋮
					</span>
				</td>

				<!-- 2. Slot Number Jumper Button -->
				<td class="py-1 px-1.5 text-center font-mono font-bold text-slate-400 text-[10px] align-middle">
					<button type="button" 
							@click="promptMoveSlot({{ json_encode($pokemon) }})" 
							class="bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-300 px-1.5 py-0.5 rounded border border-slate-700/70 transition cursor-pointer"
							title="Click to move to specific slot / page">
						#{{ sprintf('%03d', $pokemon->slot ?: $loop->iteration) }} ↗
					</button>
				</td>

				<!-- 3. Database Raw ID -->
				<td class="py-1 px-1.5 text-center font-mono text-slate-500 text-[9px] align-middle">
					{{ $pokemon->id }}
				</td>
				
				<!-- 4. Thumbnail Image -->
				<td class="py-1 px-1.5 text-center align-middle">
					<img src="{{ $pokemon->image_path ?: asset('images/placeholder.png') }}" 
						 alt="{{ $pokemon->name }}" 
						 class="w-6 h-6 object-cover rounded border border-slate-700 mx-auto" 
						 onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
				</td>
				
				<!-- 5. Name -->
				<td class="py-1 px-2 font-bold text-white tracking-wide text-[11px] align-middle whitespace-nowrap">{{ $pokemon->name }}</td>
				
				<!-- 6. Main Species -->
				<td class="py-1 px-2 text-indigo-300 font-semibold text-[10px] align-middle whitespace-nowrap">{{ $pokemon->species ?? '-' }}</td>
				
				<!-- 7. Evo Number -->
				<td class="py-1 px-1.5 text-center font-mono text-slate-300 text-[10px] align-middle whitespace-nowrap">{{ $pokemon->evo_number ?? '-' }}</td>
				
				<!-- 8. Inspiration -->
				<td class="py-1 px-2 text-slate-300 text-[10px] align-middle max-w-[140px] whitespace-normal break-words">{{ $pokemon->insp ?? '-' }}</td>
				
				<!-- 9. Type 1 -->
				<td class="py-1 px-1.5 align-middle whitespace-nowrap">
					<span class="inline-block px-1.5 py-0.2 rounded-full text-[8px] font-semibold uppercase tracking-wider border text-white"
						  style="background-color: {{ ($typeMap[$pokemon->type1]->color_hex ?? '#64748b') }}20; border-color: {{ ($typeMap[$pokemon->type1]->color_hex ?? '#64748b') }}60; color: {{ $typeMap[$pokemon->type1]->color_hex ?? '#ffffff' }};">
						{{ $pokemon->type1 }}
					</span>
				</td>
				
				<!-- 10. Type 2 -->
				<td class="py-1 px-1.5 align-middle whitespace-nowrap">
					@if($pokemon->type2)
					@php $t2 = strtolower($pokemon->type2); @endphp
					<span class="inline-block px-1.5 py-0.2 rounded-full text-[8px] font-semibold uppercase tracking-wider border {{ $types[$t2]['class'] ?? 'bg-slate-700 text-slate-300 border-slate-600' }}">
						{{ $pokemon->type2 }}
					</span>
					@else
					<span class="text-slate-600 text-[9px]">-</span>
					@endif
				</td>
				
				<!-- 11. UNTRUNCATED FULL DESCRIPTION (Expands row height as much as needed) -->
				<td class="py-1.5 px-2.5 text-slate-300 text-[11px] leading-relaxed align-middle whitespace-normal break-words min-w-[240px]">
					{{ $pokemon->description ?? '-' }}
				</td>
				
				<!-- 12. Action Buttons -->
				<td class="py-1 px-2 text-center whitespace-nowrap align-middle">
					<div class="flex items-center justify-center gap-1">
						<button type="button" @click="setEditData({{ json_encode($pokemon) }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-1.5 py-0.5 rounded text-[9px] font-medium transition-all cursor-pointer">
							Edit
						</button>
						
						<form action="{{ route('pokemon.destroy', $pokemon->id) }}" method="POST" onsubmit="return confirm('Delete {{ $pokemon->name }} permanently?');">
							@csrf
							@method('DELETE')
							<button type="submit" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-1.5 py-0.5 rounded text-[9px] font-medium transition-all cursor-pointer">
								Delete
							</button>
						</form>
					</div>
				</td>
			</tr>
			@empty
			<tr>
				<td colspan="12" class="py-6 text-center text-slate-500 text-xs">
					No Pokémon entries found. Click "+ Add Pokémon" to create one!
				</td>
			</tr>
			@endforelse
		</tbody>
	</table>
</div>
					
					<!-- GRID CARD VIEW MODE -->
					<div x-cloak x-show="viewMode === 'grid'" class="p-4">
						<div x-init="initSortable($el)" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
							@forelse($pokemons as $pokemon)
							<div data-id="{{ $pokemon->id }}" class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3.5 shadow-lg flex flex-col h-full space-y-2.5 relative group">
								
								<!-- Card Header Slot Badge -->
								<div class="flex justify-between items-center text-[10px] font-mono text-slate-400 gap-2 shrink-0">
									<div class="flex items-center gap-1.5">
										<span class="drag-handle text-slate-500 hover:text-slate-200 cursor-grab active:cursor-grabbing select-none text-xs">
											⋮⋮
										</span>
										<button type="button" 
										@click="promptMoveSlot({{ json_encode($pokemon) }})" 
										class="bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 px-2 py-0.5 rounded border border-slate-700 font-bold transition cursor-pointer flex items-center gap-1"
										title="Click to move to specific slot / page">
											<span>#{{ sprintf('%03d', $pokemon->slot ?: $loop->iteration) }}</span>
											<span class="text-[8px] opacity-70">↗</span>
										</button>
									</div>
									
									@if($pokemon->species || $pokemon->evo_number)
									<span class="text-indigo-300 font-semibold uppercase tracking-wider truncate text-right">
										{{ $pokemon->species ?? '' }} {{ $pokemon->evo_number ? '(Evo '.$pokemon->evo_number.')' : '' }}
									</span>
									@endif
								</div>
								
								<!-- ENLARGED / TALLER MONSTER IMAGE SHOWCASE CONTAINER -->
								<div class="bg-slate-800/80 border border-slate-700/60 rounded-lg p-2.5 flex items-center justify-center h-44 relative overflow-hidden group-hover:bg-slate-800 transition shrink-0">
									<img src="{{ $pokemon->image_path ?: asset('images/placeholder.png') }}" 
									alt="{{ $pokemon->name }}" 
									class="max-h-40 max-w-full object-contain drop-shadow-md transform group-hover:scale-105 transition duration-200" 
									onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
								</div>
								
								<!-- Info Section -->
								<div class="space-y-1.5 flex-1">
									<h3 class="text-sm font-bold text-white tracking-wide">{{ $pokemon->name }}</h3>
									
									<div class="flex items-center gap-1.5 flex-wrap">
										@php $t1 = strtolower($pokemon->type1); @endphp
										<span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-semibold uppercase tracking-wider border {{ $types[$t1]['class'] ?? 'bg-slate-700 text-slate-300 border-slate-600' }}">
											{{ $pokemon->type1 }}
										</span>
										
										@if($pokemon->type2)
										@php $t2 = strtolower($pokemon->type2); @endphp
										<span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-semibold uppercase tracking-wider border {{ $types[$t2]['class'] ?? 'bg-slate-700 text-slate-300 border-slate-600' }}">
											{{ $pokemon->type2 }}
										</span>
										@endif
									</div>
									
									@if($pokemon->insp)
									<p class="text-[10px] text-slate-400 italic">
										<strong class="text-slate-300 font-normal">Insp:</strong> {{ $pokemon->insp }}
									</p>
									@endif
									
									@if($pokemon->description)
									<p class="text-[10px] text-slate-400 leading-relaxed whitespace-normal break-words">
										{{ $pokemon->description }}
									</p>
									@endif
								</div>
								
								<!-- Action Buttons Footer -->
								<div class="mt-auto pt-2 border-t border-slate-800 flex items-center justify-end gap-1.5 shrink-0">
									<button type="button" @click="setEditData({{ json_encode($pokemon) }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-medium transition cursor-pointer">
										Edit
									</button>
									
									<form action="{{ route('pokemon.destroy', $pokemon->id) }}" method="POST" onsubmit="return confirm('Delete {{ $pokemon->name }} permanently?');">
										@csrf
										@method('DELETE')
										<button type="submit" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded text-[10px] font-medium transition cursor-pointer">
											Delete
										</button>
									</form>
								</div>
								
							</div>
							@empty
							<div class="col-span-full py-12 text-center text-slate-500 text-xs">
								No Pokémon entries found. Click "+ Add Pokémon" to create one!
							</div>
							@endforelse
						</div>
					</div>
					
					<!-- PAGINATION BAR -->
					@if($pokemons->hasPages())
					<div class="px-4 py-3 bg-slate-900/90 border-t border-slate-700/80 flex items-center justify-between text-xs">
						<div>
							<span class="text-slate-400 text-[11px]">
								Page <strong class="text-white">{{ $pokemons->currentPage() }}</strong> of <strong class="text-white">{{ $pokemons->lastPage() }}</strong>
							</span>
						</div>
						<div class="flex items-center gap-2">
							@if ($pokemons->onFirstPage())
							<span class="px-3 py-1 rounded-md bg-slate-800 text-slate-600 border border-slate-700 cursor-not-allowed">Previous</span>
							@else
							<a href="{{ $pokemons->previousPageUrl() }}" class="px-3 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">Previous</a>
							@endif
							
							@if ($pokemons->hasMorePages())
							<a href="{{ $pokemons->nextPageUrl() }}" class="px-3 py-1 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white shadow transition">Next</a>
							@else
							<span class="px-3 py-1 rounded-md bg-slate-800 text-slate-600 border border-slate-700 cursor-not-allowed">Next</span>
							@endif
						</div>
					</div>
					@endif
					
				</div>
			</div>
			
		</div>
		
		<!-- MODAL 1: ADD POKEMON (WIDE HORIZONTAL LAYOUT) -->
		<div x-show="openAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
			<div x-show="openAddModal" @click="openAddModal = false" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
			<div class="min-h-screen px-3 flex items-center justify-center p-2">
				<div x-show="openAddModal" class="bg-slate-800 border border-slate-700 rounded-xl p-4 max-w-3xl w-full shadow-2xl relative z-10 space-y-3">
					
					<div class="flex justify-between items-center border-b border-slate-700 pb-2">
						<h3 class="text-xs font-bold text-white flex items-center gap-1.5">✨ Create Custom Pokémon</h3>
						<button @click="openAddModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
					</div>
					
					<form action="{{ route('pokemon.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
						@csrf
						
						<!-- Left Column -->
						<div class="space-y-2.5">
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Name *</label>
								<input type="text" name="name" required placeholder="e.g. Kidomo" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-indigo-500 text-xs">
							</div>
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Main Species</label>
								<input type="text" name="species" placeholder="e.g. komodo, tapir" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-indigo-500 text-xs">
							</div>
							<div class="grid grid-cols-2 gap-2">
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Evo Number</label>
									<input type="number" name="evo_number" min="1" placeholder="e.g. 1" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-indigo-500 text-xs">
								</div>
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Upload Image</label>
									<input type="file" name="image" accept="image/*" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-0.5 text-slate-300 text-[10px] file:mr-1.5 file:py-0.5 file:px-1.5 file:rounded file:border-0 file:text-[9px] file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
								</div>
							</div>
							<div class="grid grid-cols-2 gap-2">
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Type 1 *</label>
									<select name="type1" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-slate-100 focus:outline-none focus:border-indigo-500 text-xs capitalize">
										<option value="">-- Choose T1 --</option>
										@foreach(array_keys($types) as $typeOption)
										<option value="{{ $typeOption }}">{{ ucfirst($typeOption) }}</option>
										@endforeach
									</select>
								</div>
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Type 2 (Optional)</label>
									<select name="type2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-slate-100 focus:outline-none focus:border-indigo-500 text-xs capitalize">
										<option value="">-- None --</option>
										@foreach(array_keys($types) as $typeOption)
										<option value="{{ $typeOption }}">{{ ucfirst($typeOption) }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
						
						<!-- Right Column -->
						<div class="space-y-2.5 flex flex-col justify-between">
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Inspiration</label>
								<textarea name="insp" rows="2" placeholder="e.g. Phoenix, Volcanic dragons..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 text-xs resize-none"></textarea>
							</div>
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Description</label>
								<textarea name="description" 
								rows="5" 
								placeholder="Write backstory or lore..." 
								class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 text-xs min-h-[110px] resize-y"></textarea>
							</div>
							
							<div class="flex justify-end gap-2 pt-2 border-t border-slate-700/80">
								<button type="button" @click="openAddModal = false" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1 rounded-lg text-xs font-semibold">Cancel</button>
								<button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1 rounded-lg text-xs font-semibold shadow">Save Pokémon</button>
							</div>
						</div>
						
					</form>
				</div>
			</div>
		</div>
		
		<!-- MODAL 2: EDIT POKEMON (WIDE HORIZONTAL LAYOUT) -->
		<div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
			<div x-show="openEditModal" @click="openEditModal = false" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
			<div class="min-h-screen px-3 flex items-center justify-center p-2">
				<div x-show="openEditModal" class="bg-slate-800 border border-slate-700 rounded-xl p-4 max-w-3xl w-full shadow-2xl relative z-10 space-y-3">
					
					<div class="flex justify-between items-center border-b border-slate-700 pb-2">
						<h3 class="text-xs font-bold text-amber-400 flex items-center gap-1.5">
							✏️ Edit Pokémon: <span class="text-white" x-text="editPokemon.name"></span>
						</h3>
						<button @click="openEditModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
					</div>
					
					<form :action="`{{ url('/pokemon') }}/${editPokemon.id}`" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
						@csrf
						@method('PUT')
						
						<!-- Left Column -->
						<div class="space-y-2">
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Name *</label>
								<input type="text" name="name" x-model="editPokemon.name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs">
							</div>
							<div class="grid grid-cols-2 gap-2">
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Main Species</label>
									<input type="text" name="species" x-model="editPokemon.species" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs">
								</div>
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Evo Number</label>
									<input type="number" name="evo_number" x-model="editPokemon.evo_number" min="1" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs">
								</div>
							</div>
							
							<div class="grid grid-cols-2 gap-2">
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Type 1 *</label>
									<select name="type1" x-model="editPokemon.type1" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs capitalize">
										@foreach(array_keys($types) as $typeOption)
										<option value="{{ $typeOption }}">{{ ucfirst($typeOption) }}</option>
										@endforeach
									</select>
								</div>
								<div>
									<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Type 2 (Optional)</label>
									<select name="type2" x-model="editPokemon.type2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs capitalize">
										<option value="">-- None --</option>
										@foreach(array_keys($types) as $typeOption)
										<option value="{{ $typeOption }}">{{ ucfirst($typeOption) }}</option>
										@endforeach
									</select>
								</div>
							</div>
							
							<!-- Image Section -->
							<div class="space-y-1.5 bg-slate-900/60 p-2 rounded-lg border border-slate-700/80">
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider">Image Management</label>
								<template x-if="editPokemon.image_path">
									<div class="flex items-center justify-between gap-2 bg-slate-900 p-1.5 rounded border border-slate-700">
										<div class="flex items-center gap-2">
											<img :src="editPokemon.image_path" alt="Current Image" class="w-7 h-7 object-cover rounded border border-slate-700" onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
											<span class="text-[9px] text-slate-400 truncate max-w-[120px]" x-text="editPokemon.image_path"></span>
										</div>
										<label class="inline-flex items-center gap-1 text-[9px] text-rose-400 hover:text-rose-300 cursor-pointer bg-rose-500/10 px-1.5 py-0.5 rounded border border-rose-500/20">
											<input type="checkbox" name="clear_image" value="1" class="rounded border-slate-700 bg-slate-900 text-rose-600 focus:ring-rose-500 cursor-pointer">
											<span>Remove</span>
										</label>
									</div>
								</template>
								<input type="file" name="image" accept="image/*" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-0.5 text-slate-300 text-[10px] file:mr-1.5 file:py-0.5 file:px-1.5 file:rounded file:border-0 file:text-[9px] file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-500 cursor-pointer">
							</div>
						</div>
						
						<!-- Right Column -->
						<div class="space-y-2 flex flex-col justify-between">
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Inspiration</label>
								<textarea name="insp" x-model="editPokemon.insp" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none focus:border-amber-500 text-xs resize-none"></textarea>
							</div>
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-0.5">Description</label>
								<textarea name="description" 
								x-model="editPokemon.description" 
								rows="5" 
								placeholder="Write backstory or lore..." 
								class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:border-amber-500 text-xs min-h-[110px] resize-y"></textarea>
							</div>
							
							<div class="flex justify-end gap-2 pt-2 border-t border-slate-700">
								<button type="button" @click="openEditModal = false" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1 rounded-lg text-xs font-semibold">Cancel</button>
								<button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white px-4 py-1 rounded-lg text-xs font-semibold shadow">Update Changes</button>
							</div>
						</div>
						
					</form>
				</div>
			</div>
		</div>
		
		<!-- MODAL 3: IMPORT POKEMON (HORIZONTAL LAYOUT) -->
		<div x-show="openImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
			<div x-show="openImportModal" @click="openImportModal = false" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
			<div class="min-h-screen px-3 flex items-center justify-center p-2">
				<div x-show="openImportModal" class="bg-slate-800 border border-slate-700 rounded-xl p-4 max-w-xl w-full shadow-2xl relative z-10 space-y-3">
					
					<div class="flex justify-between items-center border-b border-slate-700 pb-2">
						<h3 class="text-xs font-bold text-white flex items-center gap-1.5">📥 Import Pokémon from Excel / CSV</h3>
						<button @click="openImportModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
					</div>
					
					<form action="{{ route('pokemon.import') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
						@csrf
						
						<!-- Left Column: Guidelines -->
						<div class="bg-slate-900/60 p-2.5 rounded-lg border border-slate-700/80 space-y-1 text-[10px] text-slate-400">
							<p class="font-semibold text-slate-200">Expected Column Order:</p>
							<p class="font-mono text-[9px] text-indigo-300 break-words leading-tight">Image Path, Name, Main Species, Evo Number, Inspiration, Type 1, Type 2, Description</p>
						</div>
						
						<!-- Right Column: File Input & Actions -->
						<div class="space-y-3">
							<div>
								<label class="block text-[10px] font-semibold text-slate-300 uppercase tracking-wider mb-1">Select .csv File *</label>
								<input type="file" name="file" accept=".csv" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-1 text-slate-300 text-xs file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
							</div>
							
							<div class="flex justify-end gap-2 pt-2 border-t border-slate-700">
								<button type="button" @click="openImportModal = false" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1 rounded-lg text-xs font-semibold">Cancel</button>
								<button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1 rounded-lg text-xs font-semibold shadow">Start Import</button>
							</div>
						</div>
						
					</form>
				</div>
			</div>
		</div>
		
		<!-- MODAL 4: MANAGE TYPES (WIDE HORIZONTAL GRID LAYOUT) -->
		<div x-show="openTypeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
			<div x-show="openTypeModal" @click="openTypeModal = false" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
			<div class="min-h-screen px-3 flex items-center justify-center p-2">
				<div x-show="openTypeModal" class="bg-slate-800 border border-slate-700 rounded-xl p-4 max-w-4xl w-full shadow-2xl relative z-10 space-y-3">
					
					<div class="flex justify-between items-center border-b border-slate-700 pb-2">
						<h3 class="text-xs font-bold text-white flex items-center gap-1.5">🎨 Manage Element Types</h3>
						<button @click="openTypeModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
					</div>
					
					<div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
						
						<!-- Left Side: Add New Type Form (4 Columns) -->
						<div class="md:col-span-4 bg-slate-900/60 p-3 rounded-lg border border-slate-700 space-y-2">
							<span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider block">Add New Type</span>
							<form action="{{ route('types.store') }}" method="POST" class="space-y-2">
								@csrf
								<div>
									<input type="text" name="name" required placeholder="Type Name (e.g. Shadow)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 focus:outline-none text-xs">
								</div>
								<div class="flex items-center gap-2">
									<input type="color" name="color_hex" value="#6366f1" class="w-8 h-7 bg-slate-900 border border-slate-700 rounded cursor-pointer shrink-0">
									<button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white px-3 py-1 rounded-lg text-xs font-semibold shadow">Add Type</button>
								</div>
							</form>
						</div>
						
						<!-- Right Side: Existing Types 2-Column Grid (8 Columns) -->
						<div class="md:col-span-8 space-y-1.5">
							<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Existing Types ({{ $dbTypes->count() }})</span>
							
							<div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-64 overflow-y-auto pr-1">
								@foreach($dbTypes as $type)
								<div class="flex items-center justify-between gap-1.5 bg-slate-900/80 p-1.5 rounded-lg border border-slate-700">
									<form action="{{ route('types.update', $type->id) }}" method="POST" class="flex items-center gap-1.5 flex-1">
										@csrf
										@method('PUT')
										<input type="color" name="color_hex" value="{{ $type->color_hex }}" class="w-5 h-5 bg-slate-900 border border-slate-700 rounded cursor-pointer shrink-0">
										<input type="text" name="name" value="{{ $type->name }}" class="bg-slate-800 border border-slate-700 rounded px-1.5 py-0.5 text-xs text-white flex-1 font-semibold capitalize min-w-0">
										<button type="submit" class="bg-amber-500/20 text-amber-300 border border-amber-500/30 hover:bg-amber-500/30 px-1.5 py-0.5 rounded text-[9px] font-medium">Save</button>
									</form>
									
									<form action="{{ route('types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Delete {{ $type->name }} type?');">
										@csrf
										@method('DELETE')
										<button type="submit" class="bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-rose-500/30 px-1.5 py-0.5 rounded text-[9px] font-medium">Delete</button>
									</form>
								</div>
								@endforeach
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
		
		<!-- CHART.JS ANALYTICS INITIALIZATION -->
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const typeLabels = {!! json_encode(array_map('ucfirst', $dbTypes->pluck('name')->toArray())) !!};
				const typeData = {!! json_encode(array_values($typeCounts)) !!};
				const typeColors = {!! json_encode($dbTypes->pluck('color_hex')->toArray()) !!};
				
				// 1. Bar Chart: Element / Type Counts
				const ctxBar = document.getElementById('typesBarChart').getContext('2d');
				new Chart(ctxBar, {
					type: 'bar',
					data: {
						labels: typeLabels,
						datasets: [{
							label: 'Monsters',
							data: typeData,
							backgroundColor: typeColors,
							borderRadius: 4,
							borderWidth: 0
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: { display: false },
							tooltip: {
								callbacks: {
									label: (context) => ` ${context.raw} Pokémon`
								}
							}
						},
						scales: {
							x: {
								ticks: { color: '#94a3b8', font: { size: 9 } },
								grid: { display: false }
							},
							y: {
								ticks: { color: '#94a3b8', font: { size: 9 }, stepSize: 1 },
								grid: { color: 'rgba(51, 65, 85, 0.4)' },
								beginAtZero: true
							}
						}
					}
				});
				
				// 2. Doughnut Chart: Single vs Dual Type
				const ctxDoughnut = document.getElementById('typeCompositionChart').getContext('2d');
				new Chart(ctxDoughnut, {
					type: 'doughnut',
					data: {
						labels: ['Single Type', 'Dual Type'],
						datasets: [{
							data: [{{ $singleTypeCount }}, {{ $dualTypeCount }}],
							backgroundColor: ['#38bdf8', '#c084fc'],
							borderWidth: 2,
							borderColor: '#0f172a'
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								position: 'bottom',
								labels: { color: '#94a3b8', font: { size: 10 }, boxWidth: 12 }
							}
						},
						cutout: '65%'
					}
				});
			});
		</script>
		
		<!-- ALPINE.JS APPLICATION STATE -->
		<script>
			document.addEventListener('alpine:init', () => {
				Alpine.data('pokemonApp', () => ({
					// View Mode Persistence
					viewMode: localStorage.getItem('pokemon_view_mode') || 'grid',
					setViewMode(mode) {
						this.viewMode = mode;
						localStorage.setItem('pokemon_view_mode', mode);
					},
					
					// Live Effectiveness Matrix Store
					effectiveness: @json($effectivenessMatrix),
					
					// Cycle Multipliers: 1.0 -> 2.0 -> 0.5 -> 0.0 -> 1.0
					async cycleEffectiveness(att, def) {
						const current = this.effectiveness[att]?.[def] ?? 1.0;
						let next = 1.0;
						if (current === 1.0) next = 2.0;
						else if (current === 2.0) next = 0.5;
						else if (current === 0.5) next = 0.0;
						else if (current === 0.0) next = 1.0;
						
						this.effectiveness[att][def] = next;
						
						await fetch('{{ route("type-effectiveness.update") }}', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-CSRF-TOKEN': '{{ csrf_token() }}'
							},
							body: JSON.stringify({ attacker: att, defender: def, multiplier: next })
						});
					},
					
					// Reset All Multipliers to 1x
					async resetEffectiveness() {
						if (!confirm('Reset all type effectiveness multipliers back to default 1x?')) return;
						
						for (let att in this.effectiveness) {
							for (let def in this.effectiveness[att]) {
								this.effectiveness[att][def] = 1.0;
							}
						}
						
						await fetch('{{ route("type-effectiveness.reset") }}', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-CSRF-TOKEN': '{{ csrf_token() }}'
							}
						});
					},
					
					async promptMoveSlot(pokemon) {
						const currentSlot = pokemon.slot || pokemon.id;
						const target = prompt(`Move "${pokemon.name}" to Slot #:\n(Enter any slot number across all pages)`, currentSlot);
						
						if (target === null || target === "" || isNaN(target)) return;
						
						const targetSlot = parseInt(target, 10);
						if (targetSlot === currentSlot || targetSlot < 1) return;
						
						try {
							const response = await fetch('{{ route("pokemon.moveSlot") }}', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': '{{ csrf_token() }}'
								},
								body: JSON.stringify({ id: pokemon.id, target_slot: targetSlot })
							});
							
							const result = await response.json();
							if (result.success) {
								window.location.reload();
							}
							} catch (error) {
							console.error('Failed to move slot:', error);
						}
					},
					
					// Modals & Forms State
					openAddModal: false,
					openEditModal: false,
					openImportModal: false,
					openTypeModal: false,
					
					editPokemon: { 
						id: '', 
						name: '', 
						species: '', 
						evo_number: '', 
						image_path: '', 
						insp: '', 
						type1: '', 
						type2: '', 
						description: '' 
					},
					
					setEditData(pokemon) {
						this.editPokemon = {
							id: pokemon.id,
							name: pokemon.name || '',
							species: pokemon.species || '',
							evo_number: pokemon.evo_number || '',
							image_path: pokemon.image_path || '',
							insp: pokemon.insp || '',
							type1: pokemon.type1 || '',
							type2: pokemon.type2 || '',
							description: pokemon.description || ''
						};
						this.openEditModal = true;
					},
					
					initSortable(el) {
						if (!el) return;
						
						if (typeof Sortable === 'undefined') {
							console.error('Sortable.js is not loaded!');
							return;
						}
						
						new Sortable(el, {
							handle: '.drag-handle',
							animation: 150,
							
							// --- Smooth Drag Offset Fixes ---
							forceFallback: true,        // Replaces buggy HTML5 drag image with JS positioning
							fallbackOnBody: true,       // Attaches dragged clone to <body> so grid/table bounds don't distort offset
							fallbackTolerance: 3,       // Small pixel movement buffer so clicks don't accidentally trigger drag
							fallbackClass: 'sortable-drag-active', // Custom class while dragging
							
							ghostClass: 'opacity-20',   // Style for the slot placeholder left behind
							chosenClass: 'bg-indigo-950/40',
							
							onEnd: async () => {
								const ids = Array.from(el.querySelectorAll('[data-id]'))
								.map(item => item.dataset.id)
								.filter(Boolean);
								
								if (ids.length === 0) return;
								
								try {
									await fetch('{{ route("pokemon.reorder") }}', {
										method: 'POST',
										headers: {
											'Content-Type': 'application/json',
											'X-CSRF-TOKEN': '{{ csrf_token() }}'
										},
										body: JSON.stringify({ ids })
									});
									} catch (error) {
									console.error('Failed to save order:', error);
								}
							}
						});
					}
				}));
			});
		</script>
	</body>
</html>