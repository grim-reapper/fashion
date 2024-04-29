@props(['rating', 'count' => 1,'size' => 'sm','showDetail' => true, 'isLink' => true, 'totalStars' => 5])
@php
    $avg = (float)$rating;
    $whole =  (int)$avg; // 1
    $fraction = $avg - $whole; //.3
    $fullStarCounter = floor((float)$rating);
    $starSize = ['lg' => ['width' => '24', 'height' => '26'], 'sm' => ['width' => '18', 'height' => '20']];
@endphp
@if($count > 0)
    <div class="flex items-start items-center">
        <div class="inline-flex items-center relative">
        @for($i = 1; $i <= $totalStars; $i++)
            @php
                $wd = 0;
                if($i <= $whole){
                    $wd = 100;
                }else {
                    $wd = $fraction  * 100;
                    $fraction = 0;
                }
            @endphp
            <div class="relative inline-block" style="width: {{$starSize[$size]['width']}}px; height: {{$starSize[$size]['height']}}px">
                <span class="coverr absolute overflow-hidden top-0 left-0 z-50" style="width: {{$wd}}%">
                  <svg width="{{$starSize[$size]['width']}}" height="{{$starSize[$size]['height']}}"
                       viewBox="0 0 24 26"
                       fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g filter="url(#ln0vsw5zpa)">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M17.559 21a.998.998 0 0 1-.46-.11l-5.1-2.67-5.1 2.67a1 1 0 0 1-1.45-1.06l1-5.63-4.12-4a1 1 0 0 1-.25-1 1 1 0 0 1 .81-.68l5.7-.83 2.51-5.13a1 1 0 0 1 1.8 0l2.54 5.12 5.7.83a1 1 0 0 1 .81.68 1 1 0 0 1-.25 1l-4.12 4 1 5.63a1 1 0 0 1-.4 1 1 1 0 0 1-.62.18z"
                                  fill="#c24600"/>
                        </g>
                        <defs>
                            <filter id="ln0vsw5zpa" x="-2" y="0" width="28" height="28" filterUnits="userSpaceOnUse"
                                    color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                <feColorMatrix in="SourceAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                               result="hardAlpha"/>
                                <feOffset dy="2"/>
                                <feGaussianBlur stdDeviation="1"/>
                                <feComposite in2="hardAlpha" operator="out"/>
                                <feColorMatrix values="0 0 0 0 0.645833 0 0 0 0 0.645833 0 0 0 0 0.645833 0 0 0 0.5 0"/>
                                <feBlend in2="BackgroundImageFix" result="effect1_dropShadow_967_18563"/>
                                <feBlend in="SourceGraphic" in2="effect1_dropShadow_967_18563" result="shape"/>
                            </filter>
                        </defs>
                    </svg>
            </span>
                <span class="inline-block absolute overflow-hidden top-0 left-0">
                        <svg width="{{$starSize[$size]['width']}}" height="{{$starSize[$size]['height']}}"
                             viewBox="0 0 24 26"
                             fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#7o95k9nsea)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M17.559 21a.998.998 0 0 1-.46-.11l-5.1-2.67-5.1 2.67a1 1 0 0 1-1.45-1.06l1-5.63-4.12-4a1 1 0 0 1-.25-1 1 1 0 0 1 .81-.68l5.7-.83 2.51-5.13a1 1 0 0 1 1.8 0l2.54 5.12 5.7.83a1 1 0 0 1 .81.68 1 1 0 0 1-.25 1l-4.12 4 1 5.63a1 1 0 0 1-.4 1 1 1 0 0 1-.62.18zM12 16.1a.921.921 0 0 1 .46.11l3.77 2-.72-4.21a1 1 0 0 1 .29-.89l3-2.93-4.2-.62a1 1 0 0 1-.71-.56L12 5.25 10.11 9a1 1 0 0 1-.75.54l-4.2.62 3 2.93a1 1 0 0 1 .29.89l-.72 4.16 3.77-2a.92.92 0 0 1 .5-.04z"
                              fill="#C2C2BE"/>
                    </g>
                    <defs>
                        <filter id="7o95k9nsea" x="-2" y="0" width="28" height="28" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feOffset dy="2"/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix values="0 0 0 0 0.645833 0 0 0 0 0.645833 0 0 0 0 0.645833 0 0 0 0.5 0"/>
                            <feBlend in2="BackgroundImageFix" result="effect1_dropShadow_1004_21758"/>
                            <feBlend in="SourceGraphic" in2="effect1_dropShadow_1004_21758" result="shape"/>
                        </filter>
                    </defs>
                </svg>
            </span>
            </div>
            @endfor
        </div>
        @if($showDetail)
            <div {{$attributes->class(['inline-block text-pomegranate text-sm sm:text-base ml-[7px]', 'hover:underline decoration-pomegranate-400 hover:text-pomegranate-400 mt-[-2px]' => $isLink])}}>
                @isset($text)
                    {{$text}}
                @else
                    {{ $count }} {{ Str::plural('Review', $count) }}
                @endif
            </div>
        @endif
{{--        <meta content="{{$count}}" itemprop="reviewCount"/>--}}
    </div>
@endif