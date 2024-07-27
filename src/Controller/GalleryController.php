<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Habitat;
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

#[Route('api/gallery', name:'app_api_gallery_')]
class GalleryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private GalleryRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
}
    #[Route(methods: ['POST'])]
    public function uploadImage(Request $request, EntityManagerInterface $em): Response
    {
        // Get the uploaded file
        $file = $request->files->get('image');
        $title = $request->request->get('title');
        $habitatId = $request->request->get('habitat_id');

        // Check if file and title are provided
        if (!$file || !$title || !$habitatId) {
            return new JsonResponse(['error' => 'Missing image or title'], Response::HTTP_BAD_REQUEST);
        }

        // Define the directory to upload
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/img';

        // Generate a new filename
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $filename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move the file to the directory
        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

         // Find the Habitat entity by id
         $habitat = $em->getRepository(Habitat::class)->find($habitatId);
         if (!$habitat) {
             return new JsonResponse(['error' => 'Habitat not found'], Response::HTTP_NOT_FOUND);
         }

        // Create a new Gallery entity
        $gallery = new Gallery();
        $gallery->setTitle($title);
        $gallery->setUrlImage('/img' . $newFilename);
        $gallery->setHabitat($habitat);

        // Optionally, set the supervisor if needed
        // $gallery->setSupervisor($this->getUser());

        // Persist and flush the entity
        $em->persist($gallery);
        $em->flush();

        return new JsonResponse(['message' => 'Image uploaded successfully', 'id' => $gallery->getId()], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/galley/{id}",
     *     summary="Get avis by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the avis"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gallery details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pseudo", type="string", example="Pseudo"),
     *             @OA\Property(property="commentaire", type="string", example="Commentaire"),
     *             @OA\Property(property="isVisible", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis not found"
     *     )
     * )
     */

   #[Route('/{id}', name: 'show', methods: 'GET')]
   public function show(int $id): JsonResponse
   {
       $gallery = $this->repository->findOneBy(['id' => $id]);

       if (!$gallery) {
           return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
       }

       $responseData = $this->serializer->serialize($gallery, 'json', [
        AbstractNormalizer::GROUPS => ['gallery:read', 'gallery:write'],
    ]);
       return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
   }
}
