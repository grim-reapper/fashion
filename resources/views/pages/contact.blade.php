@extends('layouts.app')

@section('content')


    <section class="contact spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="contact__content">
                        <div class="contact__address">
                            <h5>Contact info</h5>
                            <ul>
                                <li>
                                    <h6><i class="fa fa-map-marker"></i> Address</h6>
                                    <p>160 Pennsylvania Ave NW, Washington, Castle, PA 16101-5161</p>
                                </li>
                                <li>
                                    <h6><i class="fa fa-phone"></i> Phone</h6>
                                    <p><span>125-711-811</span><span>125-668-886</span></p>
                                </li>
                                <li>
                                    <h6><i class="fa fa-headphones"></i> Support</h6>
                                    <p><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="8cdff9fcfce3fef8a2fce4e3f8e3ebfeedfce4f5ccebe1ede5e0a2efe3e1">[email&#160;protected]</a></p>
                                </li>
                            </ul>
                        </div>

                        <x-status-message/>

                        <x-auth-validation-errors class="mb-4" :errors="$errors"/>

                        <div class="contact__form">
                            <h5>SEND MESSAGE</h5>
                            <form action="{{ route('contact-save') }}" method="post" id="contact-us">
                                @csrf
                                <input type="text" placeholder="Name" name="name" :value="old('name', optional(auth()->user())->name)" required maxlength="100">
                                <input placeholder="Email" id="email" type="email" name="email" :value="old('email', optional(auth()->user())->email)" required>
{{--                                <input type="text" placeholder="Website">--}}
                                <textarea placeholder="Message" name="message" id="message" required :value="old('message')"></textarea>
{{--                                <button type="submit" class="site-btn">Send Message</button>--}}
                                <button class="site-btn mt-3">
                                    {{ __('Send Message') }}
                                </button>

                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="contact__map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d48158.305462977965!2d-74.13283844036356!3d41.02757295168286!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2e440473470d7%3A0xcaf503ca2ee57958!2sSaddle%20River%2C%20NJ%2007458%2C%20USA!5e0!3m2!1sen!2sbd!4v1575917275626!5m2!1sen!2sbd" height="780" style="border:0" allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

<style>
    .text-red-500{
        color: red;
        text-transform: none;
    }
    .bg-green{
        background: green;
    }
</style>

