<div class="doc-search-container" x-show="$store.showOverlaySearch.open" x-data="initSearchOverlay()"
     x-on:keydown.esc="$store.showOverlaySearch.escape()" @click.prevent="hide(event)">
    <div class="doc-search-modal">
        <header class="search-bar">
            <div class="search-form">
                <template x-if="!loading">
                    <label for="search-input" class="magnifier-label" @click="onOptionSelect()">
                        <svg width="20" height="20" class="DocSearch-Search-Icon" viewBox="0 0 20 20">
                            <path d="M14.386 14.386l4.0877 4.0877-4.0877-4.0877c-2.9418 2.9419-7.7115 2.9419-10.6533 0-2.9419-2.9418-2.9419-7.7115 0-10.6533 2.9418-2.9419 7.7115-2.9419 10.6533 0 2.9419 2.9418 2.9419 7.7115 0 10.6533z"
                                  stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                        </svg>
                    </label>
                </template>
                <template x-if="loading">
                    <div class="DocSearch-LoadingIndicator">
                        <svg viewBox="0 0 38 38" stroke="currentColor" stroke-opacity=".5">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="2">
                                    <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                          to="360 18 18" dur="1s"
                                                          repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </div>
                </template>
                <input class="search-input" aria-autocomplete="both" aria-labelledby="docsearch-label"
                       id="docsearch-input" autocomplete="off" autocorrect="off" autocapitalize="off"
                       enterkeyhint="search" spellcheck="false" placeholder="Search..." maxlength="64" type="search"
                       value=""
                       autofocus
                       x-model="modalValue"
                       x-on:input.change.debounce.500ms="changed"
                       x-on:keydown.enter="onOptionSelect()"
                       x-on:keydown.up.prevent="onArrowUp()"
                       x-on:keydown.down.prevent="onArrowDown()"
                >
                <button x-show="modalValue != ''" type="reset" title="Clear the query" class="DocSearch-Reset"
                        aria-label="Clear the query"
                        hidden="" @click.prevent="resetQuery()">
                    <svg width="20" height="20" viewBox="0 0 20 20">
                        <path d="M10 10l5.09-5.09L10 10l5.09 5.09L10 10zm0 0L4.91 4.91 10 10l-5.09 5.09L10 10z"
                              stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round"
                              stroke-linejoin="round"></path>
                    </svg>
                </button>
            </div>
            <button class="cancel" type="reset" aria-label="cancel">Cancel</button>
        </header>
        <div class="doc-search-content custom-scroll">
            <div x-show="Object.keys(suggestions).length > 0">
                <template x-for="(options, category) in suggestions" :key="category">
                    <section class="DocSearch-Hits">
                        <div class="DocSearch-Hit-source"
                             x-html="category == 'document' ? ' Documents' : category == 'page' ? ' Topics' : ' Articles'">
                        </div>
                        <ul role="listbox" aria-labelledby="docsearch-label" id="docsearch-list"
                            class="docsearch-list"
                        >
                            <template x-for="(option, index) in options" :key="index">
                                <li
                                        :class="{'DocSearch-Hit': true, 'active': activeItem === option.index}"
                                        :id="`docsearch-item-${option.index}`" role="option"
                                        aria-selected="true"
                                        x-on:mouseenter="activeItem = option.index"
                                >
                                    <a :href="option.new_url">
                                        <div class="DocSearch-Hit-Container">
                                            <div class="DocSearch-Hit-content-wrapper">
                                            <span class="DocSearch-Hit-title truncate"
                                                  x-html="option.type == 'document' ? option.document_title : option.type == 'page' ? option.page_name : option.article_title"></span>
                                            </div>
                                            <div class="DocSearch-Hit-action" x-show="activeItem === option.index">
                                                <svg class="DocSearch-Hit-Select-Icon" width="20" height="20"
                                                     viewBox="0 0 20 20">
                                                    <g stroke="currentColor" fill="none" fill-rule="evenodd"
                                                       stroke-linecap="round"
                                                       stroke-linejoin="round">
                                                        <path d="M18 3v4c0 2-2 4-4 4H2"></path>
                                                        <path d="M8 17l-6-6 6-6"></path>
                                                    </g>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </section>
                </template>
            </div>
            <div class="start-screen">
                <p class="search-help" x-show="modalValue == ''">Search documents, topics and
                    articles</p>
                <p class="search-help" x-show="modalValue != '' && Object.keys(suggestions).length <= 0">No result
                    found</p>
            </div>
        </div>
        <footer class="doc-search-footer">
            <ul class="DocSearch-Commands">
                <li>
                    <kbd class="DocSearch-Commands-Key">
                        <svg width="15" height="15" aria-label="Enter key" role="img">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                               stroke-width="1.2">
                                <path d="M12 3.53088v3c0 1-1 2-2 2H4M7 11.53088l-3-3 3-3"></path>
                            </g>
                        </svg>
                    </kbd>
                    <span class="DocSearch-Label">to select</span></li>
                <li>
                    <kbd class="DocSearch-Commands-Key">
                        <svg width="15" height="15" aria-label="Arrow down" role="img">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                               stroke-width="1.2">
                                <path d="M7.5 3.5v8M10.5 8.5l-3 3-3-3"></path>
                            </g>
                        </svg>
                    </kbd>
                    <kbd class="DocSearch-Commands-Key">
                        <svg width="15" height="15" aria-label="Arrow up" role="img">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                               stroke-width="1.2">
                                <path d="M7.5 11.5v-8M10.5 6.5l-3-3-3 3"></path>
                            </g>
                        </svg>
                    </kbd>
                    <span class="DocSearch-Label">to navigate</span></li>
                <li>
                    <kbd class="DocSearch-Commands-Key">
                        <svg width="15" height="15" aria-label="Escape key" role="img">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                               stroke-width="1.2">
                                <path d="M13.6167 8.936c-.1065.3583-.6883.962-1.4875.962-.7993 0-1.653-.9165-1.653-2.1258v-.5678c0-1.2548.7896-2.1016 1.653-2.1016.8634 0 1.3601.4778 1.4875 1.0724M9 6c-.1352-.4735-.7506-.9219-1.46-.8972-.7092.0246-1.344.57-1.344 1.2166s.4198.8812 1.3445.9805C8.465 7.3992 8.968 7.9337 9 8.5c.032.5663-.454 1.398-1.4595 1.398C6.6593 9.898 6 9 5.963 8.4851m-1.4748.5368c-.2635.5941-.8099.876-1.5443.876s-1.7073-.6248-1.7073-2.204v-.4603c0-1.0416.721-2.131 1.7073-2.131.9864 0 1.6425 1.031 1.5443 2.2492h-2.956"></path>
                            </g>
                        </svg>
                    </kbd>
                    <span class="DocSearch-Label">to close</span></li>
            </ul>
        </footer>
    </div>
</div>
<script>
    function initSearchOverlay() {
        return {
            listBox: null,
            search: '',
            activeItem: -1,
            modalValue: '',
            suggestionAttribute: 'document_title',
            suggestions: [],
            total: 0,
            selectedEvent: '',
            actionURL: '/search?q=',
            open: false,
            loading: false,
            redirectURL: '',
            clickButton() {
                window.location = this.actionURL + encodeURIComponent(this.redirectURL);
            },
            resetQuery() {
                this.modalValue = '';
                this.suggestions = [];
                let input = document.getElementById('docsearch-input');
                Alpine.nextTick(() => {
                    input?.focus()
                });
            },
            changed() {
                this.activeItem = -1;
                this.suggestions = [];
                if (this.modalValue && this.modalValue.length > 3) {
                    const searchURL = '/elastic-search/' + this.modalValue;
                    this.loading = true;
                    let ctx = this;
                    axios.get(searchURL)
                        .then(function (response) {
                            ctx.suggestions = response.data.data;
                            ctx.total = response.data.total;
                        }).finally(() => {
                        ctx.open = true;
                        ctx.loading = false;
                    });
                }
            },
            hide(e) {
                if (e.target.classList.contains('doc-search-container')) {
                    this.modalValue = '';
                    this.suggestions = [];
                    Alpine.store('showOverlaySearch').open = false;
                    document.body.classList.remove('no-scroll');
                }
            },
            openListBox() {
                let list = document.querySelector('.docsearch-list');
                if (list) {
                    list.focus();
                    this.activeItem = 0;
                }
            },
            onEscape() {
                if (this.open) {
                    this.open = false;
                }
                this.activeItem = -1;
            },
            onArrowUp() {
                this.activeItem = this.activeItem < 1 ? this.total - 1 : this.activeItem - 1;
                this.scrollIntoView();
            },
            onArrowDown() {
                if (this.activeItem === null) {
                    this.activeItem = -1;
                }
                this.activeItem = this.activeItem + 1 > this.total - 1 ? 0 : this.activeItem + 1;
                this.scrollIntoView();
            },
            scrollIntoView() {
                let list = document.querySelector(`#docsearch-item-${this.activeItem}`);
                list?.scrollIntoView({
                    block: 'end',
                    behavior: 'smooth'
                });
            },
            onOptionSelect() {
                this.open = false;
                Alpine.store('showOverlaySearch').escape();
                if (this.activeItem !== null) {
                    if (this.activeItem === -1) {
                        this.redirectURL = this.modalValue;
                        this.clickButton();
                    } else {
                        let link = document.querySelector('.DocSearch-Hit.active a');
                        window.location = link.getAttribute('href');
                    }
                }
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.store('showOverlaySearch', {
            open: false,
            show() {
                this.open = true;

            },
            escape() {
                this.open = false;
                document.body.classList.remove('no-scroll');
            },
            // hide(e) {
            //     if (e.target.classList.contains('doc-search-container')) {
            //         console.log('herere');
            //         this.open = false;
            //         document.body.classList.remove('no-scroll');
            //     }
            // }
        });
        let elem = document.querySelector('.show-overlay-search');
        if (elem) {
            elem.addEventListener('click', (e) => {
                e.preventDefault();
                Alpine.store('showOverlaySearch').show();
                let input = document.getElementById('docsearch-input');
                Alpine.nextTick(() => {
                    input.focus();
                    document.body.classList.add('no-scroll');
                });
            });
        }
    });
</script>
<style>
    .doc-search-container {
        background: rgba(101, 108, 133, 0.8);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        width: 100vw;
        z-index: 50;
        box-sizing: border-box;
    }

    .doc-search-modal {
        background: #f5f6f7;
        box-shadow: inset 1px 1px 0 0 hsla(0, 0%, 100%, .5), 0 3px 8px 0 #555a64;
        border-radius: 6px;
        margin: 60px auto auto;
        max-width: 560px;
        position: relative;
    }

    .search-bar {
        display: flex;
        padding: 12px 12px 0;
    }

    .search-form {
        align-items: center;
        background: #fff;
        border-radius: 4px;
        box-shadow: inset 0 0 0 2px #0a7d6c;
        display: flex;
        height: 56px;
        margin: 0;
        padding: 0 12px;
        position: relative;
        width: 100%;
    }

    .magnifier-label {
        cursor: pointer;
        align-items: center;
        color: #0a7d6c;
        display: flex;
        justify-content: center;
        margin: 0;
        padding: 0;
    }

    .DocSearch-LoadingIndicator, .magnifier-label, .DocSearch-Reset {
        margin: 0;
        padding: 0;
    }

    .DocSearch-LoadingIndicator {
        color: #0a7d6c;
    }

    .DocSearch-Search-Icon, .DocSearch-LoadingIndicator svg {
        stroke-width: 1.6;
    }

    .DocSearch-Search-Icon svg, .DocSearch-LoadingIndicator, .magnifier-label svg {
        width: 24px;
        height: 24px;
    }

    .search-input {
        appearance: none;
        background: 0 0;
        border: 0;
        color: #1c1e21;
        flex: 1;
        font-size: 1.2em;
        height: 100%;
        outline: 0;
        padding: 0 0 0 8px;
        width: 80%;
    }

    .DocSearch-Reset {
        animation: .1s ease-in forwards b;
        appearance: none;
        background: none;
        border-radius: 50%;
        color: #000;
        padding: 2px;
        align-items: center;
        display: flex;
        justify-content: center;
        border: 0;
        cursor: pointer;
        stroke-width: 1.4;
    }

    .DocSearch-Reset:hover {
        color: #0a7d6c;
    }

    .cancel {
        display: none;
    }

    .doc-search-content {
        max-height: calc(600px - 56px - 12px - 100px);
        min-height: 12px;
        overflow-y: auto;
        padding: 0 12px;
    }

    .start-screen {
        font-size: .9em;
        margin: 0 auto;
        padding: 36px 0;
        text-align: center;
        width: 80%;
    }

    .search-help {
        font-size: .9em;
        margin: 0;
        user-select: none;
    }

    .doc-search-footer {
        align-items: center;
        background: #fff;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 -1px 0 0 #e0e3e8, 0 -3px 6px 0 rgba(69, 98, 155, .12);
        display: flex;
        flex-shrink: 0;
        height: 44px;
        justify-content: flex-start;
        padding: 0 12px;
        position: relative;
        user-select: none;
        width: 100%;
        z-index: 300;
        box-sizing: border-box;
    }

    .DocSearch-Commands {
        color: #969faf;
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .DocSearch-Commands li:not(:last-of-type) {
        margin-right: 0.8em;
    }

    .DocSearch-Commands li, .DocSearch-Commands-Key {
        align-items: center;
        display: flex;
    }

    .DocSearch-Commands-Key {
        background: linear-gradient(-225deg, #d5dbe4, #f8f8f8);
        border: 0;
        border-radius: 2px;
        box-shadow: inset 0 -2px 0 0 #cdcde6, inset 0 0 1px 1px #fff, 0 1px 2px 1px rgba(30, 35, 90, .4);
        color: #969faf;
        height: 18px;
        justify-content: center;
        margin-right: 0.4em;
        padding: 0 0 1px;
        width: 20px;
    }

    .DocSearch-Label {
        font-size: .75em;
        line-height: 1.6em;
        color: #969faf;
    }

    .DocSearch-Hit-source {
        background: #f5f6f7;
        color: #0a7d6c;
        font-size: .85em;
        font-weight: 600;
        line-height: 32px;
        margin: 0 -4px;
        padding: 8px 4px 0;
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .doc-search-content ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .DocSearch-Hit {
        border-radius: 4px;
        display: flex;
        padding-bottom: 4px;
        position: relative;
    }

    .DocSearch-Hit.active a {
        background-color: #0a7d6c;
    }

    .DocSearch-Hit a {
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px 0 #d4d9e1;
        display: block;
        padding-left: 12px;
        width: 100%;
        text-decoration: none;
    }

    .DocSearch-Hit-Container {
        align-items: center;
        color: #444950;
        display: flex;
        flex-direction: row;
        height: 56px;
        padding: 0 12px 0 0;
    }

    .DocSearch-Hit-content-wrapper {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        font-weight: 500;
        justify-content: center;
        margin: 0 8px;
        overflow-x: hidden;
        position: relative;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 80%;
    }

    .DocSearch-Hit.active .DocSearch-Hit-title, .DocSearch-Hit.active .DocSearch-Hit-action {
        color: #fff;
    }

    .DocSearch-Hit-action {
        align-items: center;
        display: flex;
        height: 22px;
        width: 22px;
    }

    input[type="search"]::-webkit-search-decoration,
    input[type="search"]::-webkit-search-cancel-button,
    input[type="search"]::-webkit-search-results-button,
    input[type="search"]::-webkit-search-results-decoration {
        display: none;
    }
</style>