@php
    $style = [
        /* Layout ------------------------------ */

        'body' => 'margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;',
        'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;',

        /* Masthead ----------------------- */

        'email-masthead' => 'padding: 25px 0; text-align: center;',
        'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;',

        'email-body' => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;',
        'email-body_inner' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0;',
        'email-body_cell' => 'padding: 35px;',

        'email-footer' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; text-align: center;background: #343d49',
        'email-footer_cell' => 'color: #AEAEAE; padding: 35px; text-align: center;',

        /* Body ------------------------------ */

        'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
        'body_sub' => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

        /* Type ------------------------------ */

        'anchor' => 'color: #3869D4;',
        'header-1' => 'margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
        'paragraph' => 'margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em;',
        'paragraph-sub' => 'margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;',
        'paragraph-center' => 'text-align: center;',

        /* Buttons ------------------------------ */

        'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                    background-color: #3869D4; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                    text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

        'button--green' => 'background-color: #22BC66;',
        'button--red' => 'background-color: #dc4d2f;',
        'button--blue' => 'background-color: #3869D4;',
    ];
    $fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
@endphp

@extends('clients.user.emails.master')
@section('content')
    <tr>
        <td style="{{ $style['email-body'] }}" width="100%">
            <table style="{{ $style['email-body_inner'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                        <!-- Greeting -->
                        <h1 style="{{ $style['header-1'] }}">
                            @if (! empty($greeting))
                                {{ $greeting }}
                            @else
                                @if ($level == 'error')
                                    Whoops!
                                @else
                                    Hello!
                                @endif
                            @endif
                        </h1>

                        <!-- Intro -->
                        @foreach ($introLines as $line)
                            <p style="{{ $style['paragraph'] }}">
                                {{ $line }}
                            </p>
                        @endforeach

                        <!-- Action Button -->
                        @if (isset($actionText))
                            <table style="{{ $style['body_action'] }}" align="center" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}"
                                            style="{{ $fontFamily }} {{ $style['button'] }} {{ $style['button--blue'] }}"
                                            class="button"
                                            target="_blank">
                                            {{ $actionText }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <!-- Outro -->
                        @foreach ($outroLines as $line)
                            <p style="{{ $style['paragraph'] }}">
                                {{ $line }}
                            </p>
                        @endforeach

                        <!-- Salutation -->
                        <p style="{{ $style['paragraph'] }}">
                            @lang('lang.regards'),<br>{{ config('settings.fsurvey') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection
