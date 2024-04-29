@props(['data', 'resultCount' => 0])
<div x-data="initSearchResult()" x-init="$watch('page', (val) => result())">
    <div class="flex items-start space-x-3">
        <div class="text-stone-700 font-manrope-semi mt-1 text-sm sm:text-base">Filters:</div>
        <div class="flex items-start flex-wrap gap-3">
            @if($resultCount['document'] && $resultCount['article'] && $resultCount['page'])
                <div x-bind:class="{'py-1 px-5 rounded-full cursor-pointer font-manrope-semi': true, 'text-white bg-black-light': page.type == 'all', 'text-primary bg-stone-200': page.type != 'all'}"
                     x-on:click="filter('all')">All
                </div>
            @endif
            @if($resultCount['document'])
                <div x-bind:class="{'text-white bg-black-light': page.type == 'document', 'bg-stone-200': page.type != 'document', 'font-manrope-semi text-primary py-1 px-5 rounded-full cursor-pointer': true}"
                     x-on:click="filter('document')">
                    {{ $resultCount['document'] }} Documents
                </div>
            @endif
            @if($resultCount['page'])
                <div x-bind:class="{'text-white bg-black-light': page.type == 'page', 'bg-stone-200': page.type != 'page', 'font-manrope-semi text-primary py-1 px-5 rounded-full cursor-pointer': true}"
                     x-on:click="filter('page')">
                    {{ $resultCount['page'] }} Topic{{$resultCount['page'] > 1 ? 's' : ''}}
                </div>
            @endif
            @if($resultCount['article'])
                <div x-bind:class="{'text-white bg-black-light':page.type == 'article', 'bg-stone-200': page.type != 'article', 'font-manrope-semi text-primary py-1 px-5 rounded-full cursor-pointer': true}"
                     x-on:click="filter('article')">
                    {{ $resultCount['article'] }} Articles
                </div>
            @endif
        </div>
    </div>
    <div class="separator my-4 sm:my-5"></div>
    <div class="">
        <div x-ref='scrolling' class="" id="scrolling">
            <template x-for="(item, index) in results">
                <div
                        :key="index"
                        class="overflow-hidden relative custom-card">
                    <div class="flex">
                        <div class="flex-grow">
                            <div class="flex items-start flex-col">
                                <div class="text-pomegranate">
                                    <h2><a x-bind:href="item.new_url"
                                       class="block"
                                       x-html="item.type == 'page' ? item.page_name : item.type == 'document' ? item.document_title : item.article_title">
                                    </a>
                                    </h2>
                                    {{--                                <x-star-rating class="mt-2 text-primary" v-if="item.type == 'document'"--}}
                                    {{--                                             :count="item.reviews_count" :rating="item.reviews_avg" size="lg"--}}
                                    {{--                                             :is-link="false"></x-star-rating>--}}
                                </div>
                                <template x-if="item.type == 'document'">
                                    <div
                                            class="sm:hidden block bg-green px-3 text-sm text-white absolute right-0 top-0">
                                        Document
                                    </div>
                                </template>
                                <template x-if="item.type == 'article'">
                                    <div class="bg-green px-3 text-sm text-white absolute right-0 top-0">
                                        Article
                                    </div>
                                </template>
                                <template x-if="item.type == 'topic'">
                                    <div class="bg-green px-3 text-sm text-white absolute right-0 top-0">
                                        Topic
                                    </div>
                                </template>
                            </div>

                            <div class="">
                                <div class="text-stone-900 mb-5 content"
                                     x-html="item.type == 'page' ? item.banner_text : item.description">
                                </div>
                                <template x-if="item.type == 'document'">
                                    <a :href="item.new_url" class="btn btn-orange">More
                                        Information &
                                        Download</a>
                                </template>
                                <template x-if="item.type == 'article'">
                                    <a :href="item.new_url" class="btn btn-orange">Read
                                        Article</a>
                                </template>
                                <template x-if="item.type == 'topic'">
                                    <a :href="item.new_url" class="btn btn-orange">Read Topic</a>
                                </template>
                            </div>
                        </div>
                        <template x-if="item.doc_icon_large">
                            <div class="w-32 flex-shrink-0 ml-4 sm:block hidden">
                                <img :src="item.doc_icon_large" alt="">
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function initSearchResult() {
            return {
                results: [],
                page: {
                    offset: 15,
                    type: "all",
                },
                data: {{Illuminate\Support\Js::from($data)}},
                init() {
                    this.results = this.result();
                    window.addEventListener("scroll", () => this.loadMore());
                },
                filtered(data) {
                    return data.filter((item) => item.type === this.page.type);
                },
                result() {
                    let filterData = this.data;
                    if (this.page.type !== "all") {
                        filterData = this.filtered(filterData);
                    }
                    return filterData.slice(0, this.page.offset);
                },
                loadMore() {
                    let element = document.getElementById('scrolling');
                    if (element.getBoundingClientRect().bottom < window.innerHeight) {
                        this.page.offset += 15;
                        this.results = this.result();
                    }
                },
                filter(type) {
                    this.page.offset = 15;
                    this.page.type = type;
                    this.results = this.result();
                }
            };
        }
    </script>
@endpush