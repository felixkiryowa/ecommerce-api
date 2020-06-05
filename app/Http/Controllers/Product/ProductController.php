<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use App\Http\Resources\Product as ProductResource;
use App\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller {
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function index() {
        return ProductResource::collection( Product::with( 'user' )->paginate( 25 ) );

    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function create() {
        //
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request ) {
        $user = JWTAuth::user();

        $validator = Validator::make( $request->all(), [
            'name' => 'required|string|unique:products',
            'description' => 'required',
            'price' => 'required',
            'picture' => 'required',
        ] );

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 400 );
        }
        $product_photo = $request->file( 'picture' );
        // File Details
        $filename = $product_photo->getClientOriginalName();
        $extension = $product_photo->getClientOriginalExtension();
        $tempPath = $product_photo->getRealPath();
        $fileSize = $product_photo->getSize();
        $mimeType = $product_photo->getMimeType();

        // Valid File Extensions
        $valid_extension = array( 'jpeg', 'png', 'gif', 'jpg' );
        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if ( in_array( strtolower( $extension ), $valid_extension ) ) {

            // Check file size
            if ( $fileSize <= $maxFileSize ) {
                $location = 'products';
                // Upload file
                $product_photo->move( $location, $filename );
                $product = Product::create( [
                    'name' => ucfirst( $request->name ),
                    'description' => $request->description,
                    'price' => $request->price,
                    'picture' => $filename,
                    'user_id' =>$user->id
                ] );
            } else {
                return response()->json( [
                    'error_file' => true,
                    'message' => 'File too large. File must be less than 2MB.',
                ], 201 );
            }
        }

        return new ProductResource( $product );
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function show( $id ) {
        $product = Product::find( $id );
        if ( $product ) {
            return new ProductResource( $product );
        } else {
            return response()->json( [
                'success' => true,
                'message' => 'Could not find a product'
            ], 404 );
        }
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function edit( Request $request ) {
        $product = Product::find( $request->product_id);
        if ( $product ) {
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->save();
            return new ProductResource( $product );
        } else {
            return response()->json( [
                'success' => true,
                'message' => 'Could not find a product'
            ], 404 );
        }
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, $id ) {
        //
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id ) {
        $product = Product::find( $id );
        if ( $product ) {
            $product->delete();
            return response()->json( [
                'success' => true,
                'message' => 'Successfully deleted your shop'
            ], 200 );
        } else {
            return response()->json( [
                'success' => true,
                'message' => 'Could not find a product'
            ], 404 );
        }
    }
}
