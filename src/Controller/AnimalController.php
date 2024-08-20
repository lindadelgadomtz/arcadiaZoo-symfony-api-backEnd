<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;

#[Route('api/animal', name: 'app_api_animal_')]
class AnimalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AnimalRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Create a new animal.
     *
     * @OA\Post(
     *     path="/api/animal",
     *     summary="Enregistrement d'un nouvel animal",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de l'animal à enregistrer",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="prenom", type="string", example="Prenom de l'animal"),
     *             @OA\Property(property="état", type="string", example="Etat de l'animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Animal enregistre avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="prenom", type="string", example="Prenom de l'animal"),
     *             @OA\Property(property="état", type="string", example="Etat de l'animal"),
     *             @OA\Property(property="race", type="array", @OA\Items(type="string", example="RACE_ID"))
     *         )
     *     )
     * )
     */
    #[Route(methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $animal = $this->serializer->deserialize(
            $request->getContent(),
            Animal::class,
            'json'
        );

        // Tell Doctrine you want to (eventually) save the animals (no queries yet) 
        $this->manager->persist($animal);
        // Actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();

        $responseData = $this->serializer->serialize($animal, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_animal_show',
            ['id' => $animal->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Get details of an animal by ID.
     *
     * @OA\Get(
     *     path="/api/animal/{id}",
     *     summary="Get animal by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the animal"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Animal details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="prenom", type="string", example="Prenom de l'animal"),
     *             @OA\Property(property="état", type="string", example="Etat de l'animal"),
     *             @OA\Property(property="race", type="array", @OA\Items(type="string", example="RACE_ID"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);

        if (!$animal) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        // Get gallery IDs
        $galleryIds = [];
        foreach ($animal->getGallery() as $gallery) {
            $galleryIds[] = $gallery->getId();
        }

        // Prepare the response data including the gallery IDs
        $responseData = $this->serializer->serialize($animal, 'json', [
            AbstractNormalizer::GROUPS => ['animal:read', 'animal:write'],
        ]);

        // Decode the serialized data to add gallery_ids
        $responseArray = json_decode($responseData, true);
        $responseArray['gallery'] = $galleryIds;

        return new JsonResponse(data: $responseArray, status: Response::HTTP_OK);
    }

    // DOCUMENTATION
    #[Route(methods: 'GET')]
    public function showAll(): JsonResponse
    {
        $animal = $this->repository->findAll();

        if (!$animal) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($animal, 'json', 
        [ 
            AbstractNormalizer::GROUPS => ['animal:read', 'animal:write'],
            //AbstractNormalizer::IGNORED_ATTRIBUTES => ['animalFeedings']
        ]);
        return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
    }


 /**
     * @OA\Get(
     *     path="/api/animal/habitat/{habitatId}",
     *     summary="Get animals by habitat ID",
     *     @OA\Parameter(
     *         name="habitatId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the habitat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of animals in the habitat",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="prenom", type="string", example="Sophie"),
     *                 @OA\Property(property="etat", type="string", example="bonne"),
     *                 @OA\Property(property="race", type="object", @OA\Property(property="id", type="integer", example=8)),
     *                 @OA\Property(property="habitat", type="object", @OA\Property(property="id", type="integer", example=1))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Habitat not found"
     *     )
     * )
     */
    
     #[Route('/habitat/{habitatId}', name: 'show_by_habitat', methods: ['GET'])]
    public function showByHabitat(int $habitatId): JsonResponse
    {
        $habitat = $this->habitatRepository->find($habitatId);

        if (!$habitat) {
            return new JsonResponse(['error' => 'Habitat not found'], Response::HTTP_NOT_FOUND);
        }

        $animals = $this->repository->findBy(['habitat' => $habitat]);

        // Prepare the response data including the gallery IDs for each animal
        $responseData = [];
        foreach ($animals as $animal) {
            $galleryIds = [];
            foreach ($animal->getGallery() as $gallery) {
                $galleryIds[] = $gallery->getUrlImage();
            }

            $animalData = $this->serializer->serialize($animal, 'json', [
                AbstractNormalizer::GROUPS => ['animal:read', 'animal:write'],
            ]);
            
            $animalArray = json_decode($animalData, true);
            $animalArray['gallery'] = $galleryIds;

            $responseData[] = $animalArray;
        }

        return new JsonResponse($responseData, Response::HTTP_OK, []);
    }


    /**
     * Update an animal by ID.
     *
     * @OA\Put(
     *     path="/api/animal/{id}",
     *     summary="Update animal by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the animal"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="prenom", type="string", example="Prenom de l'animal"),
     *             @OA\Property(property="état", type="string", example="Etat de l'animal"),
     *             @OA\Property(property="race", type="array", @OA\Items(type="string", example="RACE_ID"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Animal updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if (!$animal) {
            $this->manager->flush();
            return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    /**
     * Delete an animal by ID.
     *
     * @OA\Delete(
     *     path="/api/animal/{id}",
     *     summary="Delete animal by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the animal"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Animal deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if (!$animal) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($animal);
        $this->manager->flush();

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }
}