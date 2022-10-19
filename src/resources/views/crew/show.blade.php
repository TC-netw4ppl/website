@section('title',"View ".$crew->name."'s details'")
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Details of the crew: <b> {{$crew->name}}</b>
        </h2>
    </x-slot>

    <div>
        <div class="max-w-6xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="block mb-8">

                @can('viewAny', \App\Models\Crew::class)
                    <a href="{{ route('crew.index') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 px-4 rounded">Back to list</a>
                @endcan
                @can('update', $crew)
                    <a href="{{ route('crew.edit', $crew->id) }}"
                       class="bg-blue-200 hover:bg-blue-300 text-black font-bold py-2 px-4 rounded">Edit</a>
                @endcan
                @can("delete", $crew)
                    <form class="inline-block" action="{{ route('crew.destroy', $crew->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this crew?');">
                        <input type="hidden" name="_method" value="DELETE">
                        @csrf
                        <button type="submit"
                                class="flex-shrink-0 bg-red-200 hover:bg-red-300 text-black font-bold py-2 px-4 rounded">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 w-full">

                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt-8 flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 w-full">

                                <tr class="border-b">
                                    <th scope="col"
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 bg-white divide-y divide-gray-200">
                                        {{ $crew->id }}
                                    </td>
                                </tr>

                                <tr class="border-b">
                                    <th scope="col"
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 bg-white divide-y divide-gray-200">
                                        {{ $crew->name }}
                                    </td>
                                </tr>

                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
