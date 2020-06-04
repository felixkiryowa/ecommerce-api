<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use App\Http\Resources\ProductResource;
use App\Product;

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
        $product_photo = $request->file( 'product_photo' );
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
                    'name' => $request->name,
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

    public function show( Product $product ) {
        return new ProductResource( $product );
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, Product  $product ) {
        $user = JWTAuth::user();

        // check if currently authenticated user is the owner of the book
        if ( $user->id !== $product->user_id ) {
            return response()->json( ['error' => 'You can only edit your own product.'], 403 );
        }

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 400 );
        }
        $product_photo = $request->file( 'product_photo' );
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
                $product->update( $request->only( [
                    'name' => $request->name,
                    'description' => $request->description,
                    'price' => $request->price,
                    'picture' => $filename,
                    ] ) );
        
                return new  ProductResource( $product );
            } else {
                return response()->json( [
                    'error_file' => true,
                    'message' => 'File too large. File must be less than 2MB.',
                ], 201 );
            }
        }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function destroy( Product $product ) {
        $product->delete();
        return response()->json(null, 204);
    }
}
