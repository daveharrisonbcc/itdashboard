<div class="relative mb-6" x-data="{ isOpen: @entangle('isActive') }">
    <!-- Semi-transparent background -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-25 z-20 transition-opacity" 
         aria-hidden="true">
    </div>

    <div class="relative {{ $isActive ? 'z-30' : '' }}">
        <div class="mx-auto max-w-xl relative">
            <label for="search" class="sr-only">Search</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input
                    type="text"
                    id="search"
                    name="search"
                    wire:model.live="search"
                    wire:focus="activate"
                    x-on:click.away="$wire.deactivate(); $el.blur();"
                    class="w-full h-12 px-4 py-2 pl-10 text-gray-900 placeholder-gray-400 border-gray-300 rounded-md focus:ring-2 focus:ring-bcblue focus:border-gray-500"
                    placeholder="Search..."
                    x-ref="searchInput"
                    autocomplete="off"
                >
            </div>
            <div wire:loading.delay.flex wire:target="search" class="absolute inset-y-0 right-0 hidden items-center pr-3 pointer-events-none">
                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <div
            x-show="isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-full max-w-xl bg-white rounded-xl shadow-2xl overflow-hidden"
        >
            @if (count($results) > 0)

            @if (count($pendingPhotos) > 0)
                    <div wire:poll.500ms="loadNextPhoto"></div>
                @endif
            <ul class="max-h-96 scroll-pb-2 scroll-pt-11 space-y-2 overflow-y-auto pb-2" id="options" role="listbox">
                @foreach($results as $index => $result)
                <li class="select-none rounded-xl" id="option-1" role="option" tabindex="-1">
                    @if($result['type'] == 'event')
                    <a class="group flex p-3 hover:bg-gray-50" href="{{ $result['uri'] }}">
                        <div class="my-2">
                            <div class="w-16 text-center mr-4">
                                <div class="bg-red-700 rounded-t text-white uppercase font-bold text-xs py-1">{{ $result['month'] }}</div>
                                <div class="rounded-b border-b border-l border-r border-neutral-300 py-1 font-bold text-xl">{{ $result['day'] }}</div>
                            </div>
                        </div>
                        <div class="ml-4 flex-auto">
                        <p class="text-lg font-medium text-gray-700">{{ $result['title'] }}</p>
                        @if(!$result['allDay'])
                        <p class="text-gray-900">{{ $result['startTime'] }} - {{ $result['endTime'] }}</p>
                        @endif
                        </div>
                    </a>
                    @elseif($result['type'] == 'user')
                    <a class="group flex p-3 hover:bg-gray-50" href="{{ $result['uri'] }}" target="_blank" rel="noopener noreferrer">
                        <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-gray-400 overflow-hidden">
                            @if ($result['photo'])
                                <img class="h-full w-full" src="{{ $result['photo'] }}" alt="{{ $result['title'] }} profile picture">
                            @else
                                <svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3044.44 3044.44">
                                    <g id="c">
                                        <circle cx="1522.22" cy="1522.22" r="1522.22" fill="#b9b9b9"/>
                                        <circle cx="1522.22" cy="1381.74" r="564.95" transform="translate(-531.19 1481.07) rotate(-45)" fill="#f4f4f4"/>
                                        <path d="m1851.42,2002.82c-95.64,53.47-208.42,84.35-329.2,84.35s-233.56-30.88-329.2-84.35c-317.17,100.15-571.79,341.16-690.45,649.64,269.88,243.63,627.44,391.98,1019.65,391.98s749.77-148.35,1019.65-391.98c-118.66-308.48-373.28-549.49-690.45-649.64Z" fill="#f4f4f4"/>
                                    </g>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4 flex-auto">
                            <p class="text-sm font-bold text-gray-700">{{ $result['title'] }}</p>
                            @if(isset($result['content']))
                                <p class="text-sm font-medium text-gray-700">{{ $result['content'] }}</p>
                            @endif
                        </div>
                    </a>
                    @else
                    <a class="group flex p-3 hover:bg-gray-50" href="{{ $result['uri'] }}" @if($result['type'] === 'resource') target="_blank" rel="noopener noreferrer" @endif>
                        @if($result['type'] == 'post')
                        <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-bcblue overflow-hidden">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1866.67 1400"><g id="Layer_1-2"><path d="M1808.33,0H291.67c-32.21,0-58.33,26.12-58.33,58.33v233.33H58.33c-32.21,0-58.33,26.12-58.33,58.33v875c0,96.5,78.5,175,175,175h1458.33c128.66,0,233.33-104.68,233.33-233.33V58.33c0-32.21-26.12-58.33-58.33-58.33ZM116.67,408.33h116.67v816.67c0,32.17-26.18,58.33-58.33,58.33s-58.33-26.16-58.33-58.33V408.33ZM1750,1166.67c0,64.33-52.34,116.67-116.67,116.67H339.79c6.49-18.27,10.21-37.85,10.21-58.33V116.67h1400v1050Z" fill="#fff"/><path d="M579.69,291.67h350c32.2,0,58.33,26.14,58.33,58.33v233.33c0,32.2-26.14,58.33-58.33,58.33h-350c-32.19,0-58.33-26.14-58.33-58.33v-233.33c0-32.19,26.14-58.33,58.33-58.33Z" fill="#fff"/><path d="M1166.67,408.33h379.17c32.21,0,58.33-26.12,58.33-58.33s-26.12-58.33-58.33-58.33h-379.17c-32.21,0-58.33,26.12-58.33,58.33s26.12,58.33,58.33,58.33Z" fill="#fff"/><path d="M1166.67,641.67h379.17c32.21,0,58.33-26.12,58.33-58.33s-26.12-58.33-58.33-58.33h-379.17c-32.21,0-58.33,26.12-58.33,58.33s26.12,58.33,58.33,58.33Z" fill="#fff"/><path d="M554.17,875h991.67c32.21,0,58.33-26.12,58.33-58.33s-26.12-58.33-58.33-58.33h-991.67c-32.21,0-58.33,26.12-58.33,58.33s26.12,58.33,58.33,58.33Z" fill="#fff"/><path d="M1545.83,991.67h-991.67c-32.21,0-58.33,26.12-58.33,58.33s26.12,58.33,58.33,58.33h991.67c32.21,0,58.33-26.12,58.33-58.33s-26.12-58.33-58.33-58.33Z" fill="#fff"/></g></svg>
                        </div>
                        @elseif($result['type'] == 'page')
                        <div class="text-center">
                            <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-bcyellow overflow-hidden">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1445 1445"><path d="M1317.5 170H1275v-42.5C1275 57.19 1217.81 0 1147.5 0h-85C902.3 0 765.27 99.19 708.41 239.21 669.55 196.95 614.32 170 552.5 170h-425C57.19 170 0 227.19 0 297.5v850c0 70.31 57.19 127.5 127.5 127.5h425c70.31 0 127.5 57.19 127.5 127.5 0 23.49 19.01 42.5 42.5 42.5s42.5-19.01 42.5-42.5c0-8.79 1.84-17.07 2.59-25.67C779.5 1318.79 830.98 1275 892.5 1275h425c70.31 0 127.5-57.19 127.5-127.5v-850c0-70.31-57.19-127.5-127.5-127.5Zm-255-85h85c23.41 0 42.5 19.09 42.5 42.5v850c0 23.41-19.09 42.5-42.5 42.5h-85c-120.03 0-227.36 55.53-297.5 142.36V382.5C765 218.48 898.48 85 1062.5 85Zm-510 1105h-425c-23.41 0-42.5-19.09-42.5-42.5v-850c0-23.41 19.09-42.5 42.5-42.5h425c70.31 0 127.5 57.19 127.5 127.5v851.33c-35.65-27.01-79.47-43.83-127.5-43.83Zm807.5-42.5c0 23.41-19.09 42.5-42.5 42.5h-425c-14.68 0-28.77 2.26-42.62 5.1 54.06-55.42 129.27-90.1 212.62-90.1h85c70.31 0 127.5-57.19 127.5-127.5V255h42.5c23.41 0 42.5 19.09 42.5 42.5v850Zm-765-680c0 23.49-19.01 42.5-42.5 42.5h-340c-23.49 0-42.5-19.01-42.5-42.5s19.01-42.5 42.5-42.5h340c23.49 0 42.5 19.01 42.5 42.5Zm0 170c0 23.49-19.01 42.5-42.5 42.5h-340c-23.49 0-42.5-19.01-42.5-42.5s19.01-42.5 42.5-42.5h340c23.49 0 42.5 19.01 42.5 42.5Zm0 170c0 23.49-19.01 42.5-42.5 42.5h-340c-23.49 0-42.5-19.01-42.5-42.5s19.01-42.5 42.5-42.5h340c23.49 0 42.5 19.01 42.5 42.5Zm0 170c0 23.49-19.01 42.5-42.5 42.5h-340c-23.49 0-42.5-19.01-42.5-42.5s19.01-42.5 42.5-42.5h340c23.49 0 42.5 19.01 42.5 42.5Z" fill="#fff"/></svg>
                            
                            </div>
                            <span class="text-xs uppercase">Page</span>
                        </div>
                        @elseif($result['type'] == 'resource')
                        <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-emerald-400 overflow-hidden">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 890.05 812.54"><g id="Layer_1-2"><path d="M477.77,378.14c-10.66-1.16-21.13-5.62-29.46-13.96-.58-.58-1.16-1.16-1.74-1.55,11.05,3.68,21.52,8.72,31.21,15.51Z" fill="#fff"/><path d="M830.18,350.22l-166.51,166.51c-40.12,40.12-92.66,60.09-145.19,60.09h-2.52c-51.76-.58-103.32-20.55-142.67-60.09-1.94-1.94-3.88-4.07-5.23-6.4-13.57-18.8-11.82-44.97,5.23-62.03,10.66-10.66,24.81-15.31,38.57-13.96,10.86.97,21.71,5.62,30.05,13.96.39.39.97.97,1.36,1.16,42.26,41.09,109.91,40.71,151.78-1.16l166.51-166.51c42.26-42.26,42.26-111.07,0-153.33-42.26-42.06-111.07-42.06-153.33,0l-77.92,78.12c-27.33-18.42-57.38-31.98-89.17-39.93,2.33-7.37,6.2-14.15,12.02-19.97l86.65-86.65c80.05-80.06,210.32-80.06,290.18,0,80.05,80.06,80.05,210.32.19,290.18Z" fill="#fff"/><path d="M516.93,364.18c-10.66,10.66-25.01,15.31-38.96,13.96h-.19c-10.66-1.16-21.13-5.62-29.46-13.96-.58-.58-1.16-1.16-1.74-1.55-42.26-40.71-109.72-40.13-151.39,1.55l-166.51,166.51c-20.55,20.55-31.79,47.69-31.79,76.57s11.24,56.21,31.79,76.76c20.35,20.35,47.69,31.6,76.57,31.6s56.21-11.24,76.57-31.6l78.12-78.12c27.14,18.61,57.38,31.98,89.17,39.93-2.33,7.37-6.4,14.15-12.02,19.97l-86.65,86.65c-38.77,38.77-90.33,60.09-145.19,60.09s-106.42-21.32-145.19-60.09c-80.06-80.06-80.06-210.13,0-290.18l166.51-166.51c52.53-52.53,126.77-70.56,194.03-54.28.58.19,1.16.39,1.75.39,9.11,2.33,18.03,5.43,26.75,8.92,13.18,5.23,25.78,12.02,37.8,20.16,10.66,7.17,20.74,15.51,30.04,24.81,18.8,18.8,18.8,49.62,0,68.43Z" fill="#fff"/></g></svg>
                        </div>
                        @endif
                        <div class="ml-4 flex-auto">
                            <p class="text-sm font-bold text-gray-700">{{ $result['title'] }}</p>
                            @if(isset($result['content']))
                            <p class="text-sm font-medium text-gray-700">{{ $result['content'] }}</p>
                            @endif
                        </div>  
                    </a>
                    @endif
                </li>
                @endforeach
            </ul>
            @else
            <div class="p-4 text-sm text-gray-500">
                @if (strlen($search) > 2)
                    No results found for "{{ $search }}"
                @else
                    Type at least 3 characters to search
                @endif
            </div>
            @endif
        </div>
    </div>
</div>