<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return \response()->json(Product::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {

        $product = Product::where('code', $code)->first();

		if(!$product) return \response()->json(['error' => true, 'message' => 'Produto não encontrado'], Response::HTTP_NOT_FOUND);

		return \response()->json($product);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $code)
    {

		if(!$request->isJson()) return \response()->json(['error' => true, 'message' => 'A requisição deve conter um corpo json']);

		$data = $request->json()->all();

		$rules = [
			'status' => 'required',
			'url' => 'required',
			'creator' => 'required',
			'name' => 'required',
			'quantity' => 'required',
			'categories' => 'required',
			'brands' => 'required',
			'labels' => 'required',
			'cities' => 'required',
			'purchase_places' => 'required',
			'stores' => 'required',
			'ingredients' => 'required',
			'traces' => 'required',
			'serving_size' => 'required',
			'serving_quantity' => 'required',
			'nutriscore_score' => 'required',
			'nutriscore_grade' => 'required',
			'main_category' => 'required',
			'image_url' => 'required',
 		];

		$messages = [
			'status.required' => 'O campo status é obrigatório',
			'url.required' => 'O campo url é obrigatório',
			'creator.required' => 'O campo creator é obrigatório',
			'name.required' => 'O campo name é obrigatório',
			'quantity.required' => 'O campo quantity é obrigatório',
			'categories.required' => 'O campo categories é obrigatório',
			'brands.required' => 'O campo brands é obrigatório',
			'labels.required' => 'O campo labels é obrigatório',
			'cities.required' => 'O campo cities é obrigatório',
			'purchase_places.required' => 'O campo purchase_places é obrigatório',
			'stores.required' => 'O campo stores é obrigatório',
			'ingredients.required' => 'O campo ingredients é obrigatório',
			'traces.required' => 'O campo traces é obrigatório',
			'serving_size.required' => 'O campo serving_size é obrigatório',
			'serving_quantity.required' => 'O campo serving_quantity  é obrigatório',
			'nutriscore_score.required' => 'O campo nutriscore_score  é obrigatório',
			'nutriscore_grade.required' => 'O campo nutriscore_grade  é obrigatório',
			'main_category.required' => 'O campo main_category  é obrigatório',
			'image_url.required' => 'O campo image_url é obrigatório',
		];

		$validator = Validator::make($data, $rules, $messages);

		if($validator->fails()) return \response()->json(['error' => true, 'message' => $validator->errors()->first()], Response::HTTP_BAD_REQUEST);

		$product = Product::where('code', $code)->first();

		if(!$product) return \response()->json(['error' => true, 'message' => 'Produto não encontrado'], Response::HTTP_NOT_FOUND);
		
		$product->status = $data['status'];

		$product->url = $data['url'];
		
		$product->creator = $data['creator'];
		
		$product->name = $data['name'];
		
		$product->quantity = $data['quantity'];
		
		$product->brands = $data['brands'];
		
		$product->categories = $data['categories'];
		
		$product->labels = $data['labels'];
		
		$product->cities = $data['cities'];
		
		$product->purchase_places = $data['purchase_places'];
		
		$product->stores = $data['stores'];
		
		$product->ingredients = $data['ingredients'];
		
		$product->traces = $data['traces'];
		
		$product->serving_size = $data['serving_size'];
		
		$product->serving_quantity = $data['serving_quantity'];
		
		$product->nutriscore_score = $data['nutriscore_score'];
		
		$product->nutriscore_grade = $data['nutriscore_grade'];
		
		$product->main_category = $data['main_category'];
		
		$product->image_url = $data['image_url'];

		$product->update();

		return \response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function destroy($code)
    {

		$product = Product::where('code', $code)->first();

		if(!$product) return \response()->json(['error' => true, 'message' => 'Produto não encontrado'], Response::HTTP_NOT_FOUND);

		$product->delete();

		return \response()->json(['error' => false, 'message' => 'Produto excluido permanentemente']);

    }
}
