<div class="gw-overlay-box gw-overlay-visible" data-gw-overlay="overlay-box" x-show="showModal"
     x-on:click="showModal = false; isPdf = false; isReview = false; lawyerAssist=false; drafting=false;"></div>
<div class="gw-overlay-wrapper gw-overlay-in" id="gw-overlay" x-show="showModal">
    <div class="gw-overlay-inner">
        <div class="gw-close-circle-icon gw-close-icon" data-gw-close="gw-close"
             x-on:click="showModal = false; isPdf = false; isReview = false;lawyerAssist=false; drafting=false;"></div>
        <div class="gw-overlay-content overlay-inner" id="overlay-contents">
            {{$slot}}
        </div>
    </div>
</div>
@push('styles')
    <style>
        .gw-overlay-box {
            background: rgba(0, 0, 0, .6);
            height: 100%;
            left: 0;
            opacity: 0;
            position: fixed;
            top: 0;
            -webkit-transition-duration: .4s;
            transition-duration: .4s;
            visibility: hidden;
            width: 100%;
            z-index: 10600
        }

        .gw-overlay-visible {
            opacity: 1;
            visibility: visible
        }

        .gw-overlay-wrapper {
            border-radius: 7px;
            height: 95%;
            left: 50%;
            max-width: 50rem;
            opacity: 0;
            position: fixed;
            top: 50%;
            -webkit-transform: translateZ(0) scale(1.185);
            transform: translateZ(0) scale(1.185);
            -webkit-transition-property: -webkit-transform, opacity;
            -moz-transition-property: -moz-transform, opacity;
            -ms-transition-property: -ms-transform, opacity;
            -o-transition-property: -o-transform, opacity;
            transition-property: transform, opacity;
            width: 100%;
            z-index: 11000
        }

        .gw-overlay-in {
            opacity: 1;
            transform: translate3d(-50%, -50%, 0) scale(1);
            -webkit-transition-duration: .4s;
            transition-duration: .4s
        }

        .gw-overlay-inner {
            background: #f8f5f1;
            border-radius: 4px;
            box-sizing: border-box;
            height: 98%;
            margin: 10px;
            padding: 1rem;
            position: relative;
            z-index: 10000
        }

        .gw-overlay-content {
            height: 100%;
            overflow-y: auto;
            text-align: left
        }

        .gw-overlay-content::-webkit-scrollbar {
            width: 0
        }

        .gw-overlay-content::-webkit-scrollbar-track {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3)
        }

        .gw-overlay-content::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .5)
        }

        .gw-close-circle-icon {
            cursor: pointer;
            height: 28px;
            position: absolute;
            right: -7px;
            top: -11px;
            width: 28px;
            z-index: 99
        }

        .gw-close-icon {
            background: #eee;
            border-radius: 50%;
            box-shadow: 0 0 2px 0 #000;
            box-sizing: border-box;
            height: 30px;
            padding: 4px;
            width: 30px
        }

        .gw-close-icon:after, .gw-close-icon:before {
            background-color: #333;
            content: " ";
            height: 12px;
            left: 14px;
            position: absolute;
            top: 9px;
            width: 2px
        }

        .gw-close-icon:before {
            transform: rotate(45deg)
        }

        .gw-close-icon:after {
            transform: rotate(-45deg)
        }
    </style>
@endpush