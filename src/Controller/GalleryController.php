<?php 
namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Habitat;
use App\Entity\Animal;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('api/gallery', name: 'app_api_gallery_')]
class GalleryController extends AbstractController
{
    private EntityManagerInterface $manager;
    private GalleryRepository $repository;
    private SerializerInterface $serializer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $manager, 
        GalleryRepository $repository,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @OA\Post(
     *     path="/api/gallery",
     *     summary="Add a picture",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Add a picture",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Sample Image"),
     *             @OA\Property(property="image_data", type="string", format="binary"),
     *             @OA\Property(property="habitat", type="integer", example=1),
     *             @OA\Property(property="animal", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sample Image"),
     *             @OA\Property(property="url_image", type="string", example="/img/sample-image-unique-id.jpg"),
     *             @OA\Property(property="habitat", type="integer", example=1),
     *             @OA\Property(property="animal", type="array", @OA\Items(type="integer"))
     *         )
     *     )
     * )
     */
    #[Route(methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        $file = $request->files->get('image');
        $title = $request->request->get('title');
        $habitatId = $request->request->get('habitat');
        $animalIds = $request->request->get('animal');
    
        if (!$file || !$title || (!$habitatId && !$animalIds)) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
    
        // Decode animalIds if it is a JSON string
        if (is_string($animalIds)) {
            $animalIds = json_decode($animalIds, true);
        }
    
        if (!is_array($animalIds)) {
            return new JsonResponse(['error' => 'Animal IDs should be an array'], Response::HTTP_BAD_REQUEST);
        }
    
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/img';
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $filename . '-' . uniqid() . '.' . $file->guessExtension();
    
        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
        $habitat = $this->manager->getRepository(Habitat::class)->find($habitatId);
        if (!$habitat) {
            return new JsonResponse(['error' => 'Habitat not found'], Response::HTTP_NOT_FOUND);
        }
    
        $animalEntities = [];
        foreach ($animalIds as $animalId) {
            $animal = $this->manager->getRepository(Animal::class)->find($animalId);
            if (!$animal) {
                return new JsonResponse(['error' => 'Animal with ID ' . $animalId . ' not found'], Response::HTTP_NOT_FOUND);
            }
            $animalEntities[] = $animal;
        }
    
        $gallery = new Gallery();
        $gallery->setTitle($title);
        $gallery->setUrlImage('/img/' . $newFilename);
        $gallery->setHabitat($habitat);
    
        foreach ($animalEntities as $animal) {
            $gallery->addAnimal($animal);
        }
    
        $this->manager->persist($gallery);
        $this->manager->flush();
    
        return new JsonResponse([
            'message' => 'Image uploaded successfully',
            'id' => $gallery->getId(),
            'title' => $gallery->getTitle(),
            'url_image' => $gallery->getUrlImage(),
            'habitat' => $gallery->getHabitat() ? $gallery->getHabitat()->getId() : null,
            'animals' => array_map(fn($animal) => $animal->getId(), $gallery->getAnimals()->toArray()),
        ], Response::HTTP_CREATED);
    }
    

    /**
     * @OA\Get(
     *     path="/api/gallery/{id}",
     *     summary="Get image by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the image"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sample Image"),
     *             @OA\Property(property="url_image", type="string", example="/img/sample-image-unique-id.jpg"),
     *             @OA\Property(property="habitat", type="integer", example=1),
     *             @OA\Property(property="animals", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $gallery = $this->repository->find($id);

        if (!$gallery) {
            return new JsonResponse(['error' => 'Image not found'], Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($gallery, 'json', [
            AbstractNormalizer::GROUPS => ['gallery:read']
        ]);

        // Decode the serialized data to add habitat_id and animals
        $responseArray = json_decode($responseData, true);

        $responseArray['habitat'] = $gallery->getHabitat() ? $gallery->getHabitat()->getId() : null;
        $responseArray['animals'] = array_map(fn($animal) => $animal->getId(), $gallery->getAnimals()->toArray());

        return new JsonResponse($responseArray, Response::HTTP_OK);
    }
}
