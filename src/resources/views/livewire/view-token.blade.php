<x-jet-action-section>
    <x-slot name="title">
        {{ __('Token administration') }}
    </x-slot>

    <x-slot name="description">
        {{ __('View your personal API token.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('If necessary, you can view your personal API token. It is used in your Android App to authenticate you.') }}
        </div>

        <div class="flex items-center mt-5">
            <x-jet-button wire:click="confirmViewToken" wire:loading.attr="disabled">
                {{ __('View my private token') }}
            </x-jet-button>
        </div>

        <!-- Log Out Other Devices Confirmation Modal -->
        <x-jet-dialog-modal wire:model="confirmingViewToken">
            <x-slot name="title">
                {{ __('View your API Token') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please enter your password to confirm your identity.') }}
                <div class="mt-4" x-data="{}" x-on:confirming-display-view-token.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-jet-input type="password" class="mt-1 block w-3/4"
                                 placeholder="{{ __('Password') }}"
                                 x-ref="password"
                                 wire:model.defer="password"
                                 wire:keydown.enter="DisplayViewToken" />

                    <x-jet-input-error for="password" class="mt-2" />
                </div>
                <div>
                    <label for="Token">{{$this->userToken}}</label>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-jet-button class="ml-2"
                              wire:click="DisplayViewToken"
                              wire:loading.attr="disabled">
                    {{ __('Confirm') }}
                </x-jet-button>
                <x-jet-secondary-button wire:click="$toggle('confirmingViewToken')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>
            </x-slot>
        </x-jet-dialog-modal>
    </x-slot>
</x-jet-action-section>

      {{$this->userToken}}
