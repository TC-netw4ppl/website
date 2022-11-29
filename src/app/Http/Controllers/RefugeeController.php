<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRefugeeRequest;
use App\Http\Requests\StoreRefugeeApiRequest;
use App\Http\Requests\StoreRefugeeRequest;
use App\Http\Requests\UpdateRefugeeRequest;
use App\Models\ApiLog;
use App\Models\Crew;
use App\Models\Field;
use App\Models\Link;
use App\Models\Refugee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


class RefugeeController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->authorizeResource(Refugee::class,
            'person');
    }

    /**
     * This function is used to return the get person API request. It should be used only by the API.
     * It returns the list of all the person for a given crew. By default, the crew of the user, if a crew id is given, it will return the person of the given crew.
     * It never returns the person that are not in the given crew.
     * The person must have get permission to be returned.
     *
     * @param  Request  $request
     * @param  Crew  $crew
     * @return Response
     */

    public static function apiGetPerson(Request $request,
        Crew $crew = null) {
        $log = ApiLog::createFromRequest($request,
            "Refugee");
        if ($request->user()->tokenCan("read")) {
            if ($crew == null) {
                $crew = $request->user()->crew;
            }
            $persons = $crew->persons;

            $responseArray = Refugee::formatRefugeesData($persons);


            return response(json_encode($responseArray),
                200,
                ['Content-type' => 'application/json']);
        }
        $log->update(["response" => "Bad token access"]);
        return response("Your token can't be use to get data",
            403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRefugeeRequest  $request
     * @param  Refugee  $person
     * @return RedirectResponse
     */
    public function update(UpdateRefugeeRequest $request,
        Refugee $person) {

        $ids = array_column($person->fields->toArray(),
            "id");
        $values = array_column(array_column($person->fields->toArray(),
            "pivot"),
            "value");
        $old = array_combine($ids,
            $values);


        foreach (array_diff($request->validated(),
            $old) as $key => $value) {
            if (!empty($value)) {
                if ($person->fields()->wherePivot('field_id',
                    $key)->exists()) {
                    $person->fields()->updateExistingPivot($key,
                        ["value" => $value]);
                } else {
                    $person->fields()->attach([$key => ['value' => $value]]);
                }
                unset($old[$key]);
                unset($request->validated()[$key]);
            }
        }


        //detach fields that are not in the request
        foreach (array_diff($old,
            $request->validated()) as $key => $value) {
            $person->fields()->detach($key);
        }
        //$refugee["date"] = ((isset($refugee["date"]) && !empty($refugee["date"])) ? $refugee["date"] : date('Y-m-d H:i', time()));

        $log = ApiLog::createFromRequest($request,
            "Refugee");
        $refugee = [];
        $refugee["api_log"] = $log->id;

        $person->update($refugee);
        return redirect()->route("person.index");
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index() {


        $fields = Field::where("crew_id",
            Auth::user()->crew->id)->where("descriptive_value",
            1)->orderBy("order")->get();

        $refugees = Auth::user()->crew->persons()->orderByDesc("date")->get();
        $refugees = Refugee::formatRefugeesData($refugees);

        return view("person.index",
            compact("refugees",
                "fields"));
    }

    /**
     * Display the specified resource.
     *
     * @param  Refugee  $person
     * @return View
     **/
    public function show(Refugee $person) {
        $fields = Field::where("status",
            ">",
            0)->where("crew_id",
            Auth::user()->crew->id)->orderBy("required")->orderBy("order")->get();

        $links = Link::where("from",
            $person->id)->orWhere("to",
            $person->id)->get();

        return view("person.show",
            compact("person",
                "fields",
                "links"));
    }

    /**
     * Show the form for creating a new resource from a json file.
     *
     * @return View
     */
    public function createFromJson() {

        return view("person.create_from_json");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRefugeeRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreRefugeeRequest $request) {
        $fields = $request->validated();
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $ref[$key] = ["value" => $value];
            }
        }
        //
        $refugee["date"] = date('Y-m-d H:i',
            time());
        $log = ApiLog::createFromRequest($request,
            "Refugee");
        $refugee["api_log"] = $log->id;
        $new_ref = Refugee::create($refugee);
        $new_ref->fields()->attach($ref);
        if ($new_ref == null) {
            $log->update(["response" => "Error in creation"]);
        }

        return redirect()->route("person.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create() {

        $fields = Field::where("status",
            ">",
            0)->where("crew_id",
            Auth::user()->crew->id)->orderBy("required")->orderBy("order")->get();

        return view("person.create",
            compact("fields"));
    }

    /**
     * Store a newly created resource from json file in storage.
     *
     * @param  FileRefugeeRequest  $request
     * @return RedirectResponse
     */
    public function storeFromJson(FileRefugeeRequest $request) {

        $log = ApiLog::createFromRequest($request,
            "Refugee");
        foreach ($request->validated() as $refugee) {
            $refugee["date"] = ((isset($refugee["date"]) && !empty($refugee["date"])) ? $refugee["date"] : date('Y-m-d H:i',
                time()));
            $refugee["api_log"] = $log->id;
            $stored_ref = Refugee::handleApiRequest($refugee);
            if ($stored_ref == null) {
                $log->update(["response" => "Error while creating a refugee"]);
            }
        }
        return redirect()->route("person.index");
    }

    /**
     * Handle the API request
     *
     * @param  StoreRefugeeApiRequest  $request
     * @return Response
     */
    public static function handleApiRequest(StoreRefugeeApiRequest $request) {
        $responseArray = [];
        $log = ApiLog::createFromRequest($request,
            "Refugee");
        if ($request->user()->tokenCan("update")) {
            foreach ($request->validated() as $person) {
                $person["api_log"] = $log->id;
                $person["application_id"] = $log->application_id;

                $stored_person = Refugee::handleApiRequest($person);

                if ($stored_person instanceof Response) {
                    return $stored_person;
                } elseif ($stored_person instanceof Refugee) {
                    array_push($responseArray,
                        $stored_person->id);
                }

            }
            return response(json_encode($responseArray),
                201,
                ['Content-type' => 'application/json']);
        }
        $log->update(["response" => "Bad token access"]);
        return response("Your token can't be use to send datas",
            403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Refugee  $person
     * @return View
     */
    public function edit(Refugee $person) {

        $fields = Field::where("status",
            ">",
            0)->where("crew_id",
            Auth::user()->crew->id)->orderBy("required")->orderBy("order")->get();

        $labels = array_column($person->fields->toArray(),
            "label");
        $values = array_column(array_column($person->fields->toArray(),
            "pivot"),
            "value");
        $refugee_detail = array_combine($labels,
            $values);

        return view("person.edit",
            compact("person",
                "fields",
                "refugee_detail"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  String  $refugee_id
     * @return RedirectResponse
     */
    public function fixDuplicatedReference(Request $request,
        $refugee_id) {

        $refugee["unique_id"] = $request->input('unique_id');

        Refugee::find($refugee_id)->update($refugee);

        return redirect(URL::previous());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Refugee  $person
     * @return RedirectResponse
     */
    public function destroy(Refugee $person) {
        $person->delete();
        return redirect()->route("person.index");
    }
}
