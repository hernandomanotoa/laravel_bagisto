<x-admin::layouts>
    <!-- Title of the page. -->
    <x-slot:title>
        @lang('admin::app.configuration.index.title')
    </x-slot>

    <!-- Heading of the page -->
    <div class="flex justify-between items-center mb-7">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.configuration.index.title')
        </p>
    </div>

    <!-- Page Content -->
    <div class="grid gap-y-8">
        @foreach ($config->items as $itemKey => $item)
            <div>
                <div class="grid gap-1">
                    <!-- Title of the Main Card -->
                    <p class="text-gray-600 dark:text-gray-300 font-semibold">
                        @lang($item['name'] ?? '')
                    </p>

                    <!-- Info of the Main Card -->
                    <p class="text-gray-600 dark:text-gray-300">
                        @lang($item['info'] ?? '')
                    </p>
                </div>

                <div class="grid grid-cols-4 gap-12 flex-wrap justify-between p-4 mt-2 bg-white dark:bg-gray-900 rounded box-shadow max-1580:grid-cols-3 max-xl:grid-cols-2 max-sm:grid-cols-1">
                    <!-- Menus cards -->
                    @foreach ($item['children'] as $childKey =>  $child)
                        <a 
                            class="flex items-center gap-2 max-w-[360px] p-2 rounded-lg transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                            href="{{ route('admin.configuration.index', ($itemKey . '/' . $childKey)) }}"
                        >
                            @if (isset($child['icon']))
                                <img
                                    class="w-[60px] h-[60px] dark:invert dark:mix-blend-exclusion"
                                    src="{{ bagisto_asset('images/' . $child['icon'] ?? '') }}"
                                >
                            @endif

                            <div class="grid">
                                <p class="mb-1.5 text-base text-gray-800 dark:text-white font-semibold">
                                    @lang($child['name'])
                                </p>
                                
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang($child['info'] ?? '')
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-admin::layouts>