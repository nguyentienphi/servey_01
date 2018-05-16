<ul class="clearfix form-wrapper ul-preview ul-content-preview" id="section-{{ $section->order }}">
    <li class="form-line content-title-section">
        <div class="form-group">
            <h3 class="title-section {{ $section->order }}" id="section-id-preview" data-order="{{ $section->order }}">
                {{ $section->title }}
            </h3>
        </div>
        <div class="form-group form-group-description-section">
            <span class="description-section">{!! $section->description !!}</span>
        </div>
    </li>
    @php
        $indexQuestion = config('settings.number_0');
    @endphp
    @foreach ($section->questions as $question)
        @php
            $questionSetting = $question->type;
            $countQuestionMedia = $question->media->count();
        @endphp
        <li class="li-question-review form-line">
            <!-- tittle -->
            @if ($questionSetting == config('settings.question_type.title'))
                @include ('clients.survey.detail.elements.title')
            <!-- video -->
            @elseif ($questionSetting == config('settings.question_type.video'))
                @include ('clients.survey.detail.elements.video')
            <!-- image -->
            @elseif ($questionSetting == config('settings.question_type.image'))
                @include ('clients.survey.detail.elements.image')
            @else
                <h4 class="title-question question-survey {{ $question->required ? 'required-question' : '' }}"
                    data-type="{{ $questionSetting }}" data-id="{{ $question->id }}"
                    data-required="{{ $question->required }}">
                    <span class="index-question">{{ ++ $indexQuestion }}</span>{{ $question->title }}
                    @if ($question->required)
                        <span class="notice-required-question"> *</span>
                    @endif
                </h4>
                <div class="form-group">
                    <span class="description-question">{!! $question->description !!}</span>
                </div>
                @if ($countQuestionMedia)
                    <div class="img-preview-question-survey">
                        {!! Html::image($question->url_media) !!}
                    </div>
                @endif
                <!-- short answer -->
                @if ($questionSetting == config('settings.question_type.short_answer'))
                    @include ('clients.survey.detail.elements.short-question')
                <!-- long answer -->
                @elseif ($questionSetting == config('settings.question_type.long_answer'))
                    @include ('clients.survey.detail.elements.long-question')
                <!-- multi choice -->
                @elseif ($questionSetting == config('settings.question_type.multiple_choice'))
                    @include ('clients.survey.detail.elements.multiple-choice')
                <!-- check boxes -->
                @elseif ($questionSetting == config('settings.question_type.checkboxes'))
                    @include ('clients.survey.detail.elements.checkboxes')
                <!-- date -->
                @elseif ($questionSetting == config('settings.question_type.date'))
                    @include ('clients.survey.detail.elements.date')
                <!-- time -->
                @elseif ($questionSetting == config('settings.question_type.time'))
                    @include ('clients.survey.detail.elements.time')
                @endif
            @endif
            @if ($question->required)
                <div class="notice-required">@lang('lang.question_required')</div>
            @endif
        </li>
    @endforeach

    <li class="li-question-review form-line">
        @if ($currentSection > config('settings.section_order_default'))
            <a href="#" class="btn-action-preview previous-section-survey">@lang('lang.previous')</a>
        @endif
        @if ($numOfSection > $currentSection)
            <a href="#" data-url="{{ route('survey.create.do-survey', $survey->token) }}"
                class="btn-action-preview next-section-survey">@lang('lang.next')</a>
        @endif
        @if ($numOfSection == $currentSection || $numOfSection == config('settings.number_1'))
            {!! Form::button(trans('profile.send'), ['class' => 'btn-action-preview btn-action-preview-submit',
                'data-url' => route('survey.create.storeresult', $survey->token)]) !!}
        @endif
    </li>
</ul>