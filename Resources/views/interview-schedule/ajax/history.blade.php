<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    <div class="d-flex flex-wrap p-20">
        @forelse ($activity as $activ)
            <div class="card file-card w-100 rounded-0 border-0 comment mb-2">
                <div class="card-horizontal">
                    <div class="card-img my-2 ml-2">
                        <img src="{{ $activ->user->image_url }}" alt="{{ $activ->user->name }}">
                    </div>
                    <div class="card-body border-0 pl-0 py-1 mb-2">
                        <div class="d-flex flex-grow-1">
                            <h4 class="card-title f-12 font-weight-normal text-dark mr-3 mb-1">
                                {{ __('recruit::modules.job.' . $activ->details) }} <a
                                    href="{{ route('employees.show', $activ->user_id) }}"
                                    class="text-darkest-grey">{{ $activ->user->name }}</a>
                            </h4>
                        </div>
                        <div class="card-text f-11 text-lightest text-justify">
                            <span class="f-11 text-lightest">
                                {{ $activ->created_at->timezone($company->timezone)->format($company->date_format) . ' ' . $activ->created_at->timezone($company->timezone)->format($company->time_format) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-cards.no-record icon="history" :message="__('messages.noRecordFound')"/>
        @endforelse

    </div>

</div>
<!-- TAB CONTENT END -->
