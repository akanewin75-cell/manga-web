@foreach($items as $manga)
    <a href="{{ route('manga.show', ['type' => $manga['source_type'], 'id' => $manga['source_id']]) }}" class="group block">
        <div class="relative aspect-[3/4] rounded-2xl md:rounded-3xl overflow-hidden mb-2 md:mb-4 border border-lunar-border group-hover:border-lunar-accent transition-soft shadow-xl">
            <img src="@proxy($manga['cover'] ?? 'https://via.placeholder.com/300x400?text=No+Cover')" 
                class="w-full h-full object-cover transition-soft group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-soft flex items-end p-2 md:p-6">
                @if($manga['source_type'] != 'comicaso')
                <span class="bg-lunar-accent text-white text-[7px] md:text-[10px] font-bold px-1.5 py-0.5 md:px-3 md:py-1 rounded-full uppercase tracking-tighter">
                    {{ $manga['source_type'] }}
                </span>
                @endif
            </div>
        </div>
        <h3 class="font-bold text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-2 text-[10px] md:text-base leading-tight uppercase">
            {{ $manga['title'] }}
        </h3>
    </a>
@endforeach
