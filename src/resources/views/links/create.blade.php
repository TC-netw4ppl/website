<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add a link
        </h2>
    </x-slot>
    <div>
        <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="block mb-8">
                <a href="{{URL::previous() }}" class="bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 px-4 rounded">Back</a>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="post" action="{{ route('links.store') }}">
                    @csrf
                        <div class="shadow overflow-hidden sm:rounded-md">

                            <!--  Relation SECTION  -->
                            <div class="px-4 py-5 bg-white sm:p-6">

                                @php($form_elem = "refugee1")
                                <label for="{{$form_elem}}" class="block font-medium text-md text-gray-700">Refugee 1</label>

                                @php( $list = $lists["refugees"])
                                @livewire("select-dropdown", ['label' => $form_elem, 'placeholder' => '-- Select the first refugee --', 'datas' => $list])
                                @stack('scripts')

                                @error($form_elem)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!--  Relation SECTION  -->
                            <div class="px-4 py-5 bg-white sm:p-6">

                                @php($form_elem = "relation")
                                <label for="{{$form_elem}}" class="block font-medium text-md text-gray-700">Relation</label>

                                @php( $list = $lists["relations"])
                                @livewire("select-dropdown", ['label' => $form_elem, 'placeholder' => '-- Select the relation --', 'datas' => $list])
                                @stack('scripts')

                                @error($form_elem)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!--  Relation SECTION  -->
                            <div class="px-4 py-5 bg-white sm:p-6">

                                @php($form_elem = "refugee2")
                                <label for="{{$form_elem}}" class="block font-medium text-md text-gray-700">Refugee 2</label>

                                @php( $list = $lists["refugees"])
                                @livewire("select-dropdown", ['label' => $form_elem, 'placeholder' => '-- Select the second refugee --', 'datas' => $list])
                                @stack('scripts')

                                @error($form_elem)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!--  Relation SECTION  -->
                            <div class="px-4 py-5 bg-white sm:p-6">

                                @php($form_elem = "detail")
                                <label for="{{$form_elem}}" class="block font-medium text-md text-gray-700">Detail</label>

                                <input value="{{ old($form_elem)}}" type="text" name="{{$form_elem}}" id="{{$form_elem}}" class="form-input rounded-md shadow-sm mt-1 block w-full" placeholder="Father" />
                                @error($form_elem)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                                    Add
                                </button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
