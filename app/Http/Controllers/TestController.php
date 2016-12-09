<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
      $data = [
        'status'=> 'success',
        'message'=> 'Connection succesful. You have reached the standard GET route',
      ];

      return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard CREATE route',
    ];

    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard STORE route',
      'requestData'=> $request->toArray(),
    ];

    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard SHOW route',
      'id'=> $id,
    ];

    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard EDIT route',
      'id'=> $id,
    ];

    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard UPDATE route',
      'id'=> $id,
      'requestData'=> $request->toArray(),
    ];

    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = [
      'status'=> 'success',
      'message'=> 'Connection succesful. You have reached the standard DELETE route',
      'id'=> $id,
    ];
    return response()->json($data)->header('Content-Type', 'application/json; charset=utf-8');
  }
}
