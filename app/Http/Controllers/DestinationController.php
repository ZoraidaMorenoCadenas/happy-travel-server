<?php
namespace App\Http\Controllers;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $destinations = Destination::all();
        return response()->json([$destinations]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
         ]);
        try{
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('destination/image', $request->image,$imageName);
            Destination::create($request->post()+['image'=>$imageName]);
            return response()->json([
                'message'=>'¡Destino creado correctamente!'
            ]);
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'¡El destino no se pudo crear correctamente!'
            ],500);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Destination::find($id);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */

     public function update(Request $request, $id)
{
    $destination = Destination::find($id);
    
    if (!$destination) {
        return response()->json([
            'error' => true,
            'message' => 'El destino que busca no existe.',
        ]);
    }

    $request->validate([
        'description' => 'required',
        'title' => 'required',
        'location' => 'required',
        'image' => 'image|mimes:jpeg,png,jpg,gif'
    ]);

    // Verifica si se envió una imagen y guárdala si es el caso
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');

        // Elimina la imagen anterior si existe
        if ($destination->image) {
            Storage::disk('public')->delete($destination->image);
        }

        // Asigna la nueva imagen al destino
        $destination->image = $imagePath;
    }

    // Actualiza los demás campos del destino
    $destination->description = $request->input('description');
    $destination->title = $request->input('title');
    $destination->location = $request->input('location');

    // Intenta guardar el destino actualizado
    if ($destination->save()) {
        return response()->json([
            'data' => $destination,
            'message' => '¡Destino actualizado correctamente!',
        ]);
    } else {
        return response()->json([
            'error' => true,
            'message' => 'El destino no pudo actualizarse.',
        ]);
    }
}
  
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id) 
    {
        try {
            $destination = Destination::findOrFail($id);

        //    if ($destination ->user_id !== Auth::user()->id) {
        //         return response()->json(['success' => false, 'error' => 'No tienes permiso para eliminar este destino.']);
        //     }

            $imagePath = public_path($destination->image);

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $destination->delete();
            return response()->json(['success' => true, 'message' => 'Destino eliminado']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'El destino no se encontró.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }

       
    }
}