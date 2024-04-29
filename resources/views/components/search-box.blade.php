<div class="relative show-overlay-search" x-data="initSearch()">
    <div class="flex items-center gap-3">
        <div class="bg-white rounded-md w-full relative border border-stone-300 h-12">
        <span class="absolute top-3.5 left-[0.7rem]">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M8.33334 1.66663C4.65144 1.66663 1.66667 4.65139 1.66667 8.33329C1.66667 12.0152 4.65144 15 8.33334 15C9.87393 15 11.2925 14.4774 12.4214 13.5998L16.9107 18.0892L16.9893 18.1585C17.3162 18.4127 17.7889 18.3896 18.0893 18.0892C18.4147 17.7638 18.4147 17.2361 18.0893 16.9107L13.5999 12.4213C14.4774 11.2924 15 9.87389 15 8.33329C15 4.65139 12.0152 1.66663 8.33334 1.66663ZM8.33334 3.33329C11.0948 3.33329 13.3333 5.57187 13.3333 8.33329C13.3333 11.0947 11.0948 13.3333 8.33334 13.3333C5.57191 13.3333 3.33334 11.0947 3.33334 8.33329C3.33334 5.57187 5.57191 3.33329 8.33334 3.33329Z"
                  fill="#E0E0E0"/>
        </svg>
            </span>
            <input
                    name="search_term_string"
                    id="search"
                    x-model="modalValue"
                    aria-label="search"
                    autocomplete="off"
                    class="w-full pl-9 pr-12 px-4 focus:outline-none rounded-md leading-[46px]"
                    type="text"
                    x-on:input.change.debounce.500ms="changed"
                    x-on:keydown.enter="onOptionSelect()"
                    x-on:keydown.down.prevent="openListBox"
            >
            <template x-if="loading">
                <svg class="animate-spin absolute right-2 top-[14px] mr-3 h-5 w-5 text-gray-500" fill="none"
                     style="top: 10px;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                          fill="#f4511e"></path>
                </svg>
            </template>
            <div x-show="open && suggestions.length > 0" class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-50"
                 x-on:click.away="open = false">
                <ul x-ref="listBox" aria-labelledby="assigned-to-label"
                    class="max-h-56 rounded-md py-1 text-base leading-6 shadow-xs overflow-auto focus:outline-none sm:text-sm sm:leading-5 custom-scroll"
                    tabindex="-1" x-on:keydown.enter.stop.prevent="onOptionSelect()"
                    x-on:keydown.space.stop.prevent="onOptionSelect()" x-on:keydown.esc="onEscape()"
                    x-on:keydown.up.prevent="onArrowUp()"
                    x-on:keydown.down.prevent="onArrowDown()">
                    <template x-for="(option, index) in suggestions">
                        <li
                                :key="index"
                                :class="{'select-none relative py-2 pl-4 pr-4 hover:bg-primary hover:text-white cursor-pointer': true, 'text-white bg-primary': activeItem === index, 'text-gray-900': !(activeItem === index) }"
                                role="option" x-on:click="choose(index)"
                                x-on:mouseenter="activeItem = index"
                                x-on:mouseleave="activeItem = -1">
                            <div class="flex items-center space-x-3 w-full">
                <span class="font-normal block truncate flex-grow"
                      x-html="option.type == 'document' ? option.document_title : option.type == 'page' ? option.page_name : option.article_title">
                  </span>
                                <span class="text-gray-400 text-sm text-right flex-shrink-0"
                                      x-html="option.type == 'document' ? ' - document' : option.type == 'page' ? '- topic' : ' - article'">
                    </span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <button class="btn btn-orange text-white px-[15px] py-[9.78px] hover:bg-pomegranate-dark" x-on:click="onOptionSelect()">
            Search
        </button>
    </div>
</div>
@push('scripts')
    <script>
        /*   document.addEventListener('DOMContentLoaded', function () {
               document.querySelector('.show-overlay-search').addEventListener('click', () => {
                   // console.log(Alpine.$store);
                   // Alpine.$store.showOverlaySearch = true;
                   // document.querySelector('.doc-search-container').classList.toggle('hidden');
               });
           });*/

        function initSearch() {
            return {
                listBox: null,
                search: '',
                activeItem: -1,
                modalValue: '',
                suggestionAttribute: 'document_title',
                suggestions: [],
                selectedEvent: '',
                actionURL: '/search?q=',
                open: false,
                loading: false,
                redirectURL: '',
                clickButton() {
                    window.location = this.actionURL + encodeURIComponent(this.redirectURL);
                },
                changed() {
                    this.suggestions = [];
                    if (this.modalValue && this.modalValue.length > 3) {
                        const searchURL = '/elastic-search/' + this.modalValue;
                        this.loading = true;
                        let ctx = this;
                        axios.get(searchURL)
                            .then(function (response) {
                                ctx.suggestions = response.data;
                            }).finally(() => {
                            ctx.open = true;
                            ctx.loading = false;
                        });
                    }
                },
                openListBox() {
                    this.$refs.listBox.focus();
                    this.activeItem = 0;
                },
                onEscape() {
                    if (this.open) {
                        this.open = false;
                    }
                },
                onArrowUp() {
                    this.activeItem = this.activeItem < 1 ? this.suggestions.length - 1 : this.activeItem - 1;
                    this.scrollIntoView();
                },
                onArrowDown() {
                    if (this.activeItem === null) {
                        this.activeItem = -1;
                    }
                    this.activeItem = this.activeItem + 1 > this.suggestions.length - 1 ? 0 : this.activeItem + 1;
                    this.scrollIntoView();
                },
                scrollIntoView() {
                    this.$refs.listBox.children[this.activeItem + 1].scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                },
                choose(index) {
                    this.open = false;
                    let item = this.suggestions[index];
                    window.location = item.new_url;
                },
                onOptionSelect() {
                    this.open = false;
                    if (this.activeItem !== null) {
                        if (this.activeItem === -1) {
                            this.redirectURL = this.modalValue;
                            this.clickButton();
                        } else {
                            let item = this.suggestions[this.activeItem];
                            window.location = item.new_url;
                        }
                    }
                }
            }
        }
    </script>
@endpush