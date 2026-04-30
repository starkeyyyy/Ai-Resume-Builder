<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Resume;
use App\Utils\ApiResponse;
use App\Utils\ApiError;

class ResumeController
{
    public function start(Request $request, Response $response)
    {
        $apiResponse = new ApiResponse(200, null, "Welcome to Resume Builder API");
        $response->getBody()->write(json_encode($apiResponse->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createResume(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        $data = $request->getParsedBody();
        $title = $data['title'] ?? null;
        $themeColor = $data['themeColor'] ?? null;

        if (!$title || !$themeColor) {
            $apiError = new ApiError(400, "Title and themeColor are required.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $resume = Resume::create([
                'title' => $title,
                'themeColor' => $themeColor,
                'user_id' => $user->id,
                'firstName' => "",
                'lastName' => "",
                'email' => "",
                'summary' => "",
                'jobTitle' => "",
                'phone' => "",
                'address' => "",
                'experience' => [],
                'education' => [],
                'skills' => [],
                'projects' => [],
            ]);

            $apiResponse = new ApiResponse(201, ['resume' => $resume], "Resume created successfully");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [$e->getMessage()], $e->getTraceAsString());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getAllResumes(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        try {
            $resumes = Resume::where('user_id', $user->id)->get();
            $apiResponse = new ApiResponse(200, $resumes, "Resumes fetched successfully");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [], $e->getTraceAsString());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getResume(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? null;

        if (!$id) {
            $apiError = new ApiError(400, "Resume ID is required.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $resume = Resume::find($id);

            if (!$resume) {
                $apiError = new ApiError(404, "Resume not found.");
                $response->getBody()->write(json_encode($apiError->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            if ($resume->user_id !== $user->id) {
                $apiError = new ApiError(403, "You are not authorized to access this resume.");
                $response->getBody()->write(json_encode($apiError->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }

            $apiResponse = new ApiResponse(200, $resume, "Resume fetched successfully");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [], $e->getTraceAsString());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function updateResume(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? null;
        $data = $request->getParsedBody();

        if (!$id) {
            $apiError = new ApiError(400, "Resume ID is required.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $resume = Resume::where('_id', $id)->where('user_id', $user->id)->first();

            if (!$resume) {
                $apiResponse = new ApiResponse(404, null, "Resume not found or unauthorized");
                $response->getBody()->write(json_encode($apiResponse->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $resume->update($data);

            $apiResponse = new ApiResponse(200, $resume, "Resume updated successfully");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [$e->getMessage()], $e->getTraceAsString());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function removeResume(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? null;

        if (!$id) {
            $apiError = new ApiError(400, "Resume ID is required.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $resume = Resume::where('_id', $id)->where('user_id', $user->id)->first();

            if (!$resume) {
                $apiResponse = new ApiResponse(404, null, "Resume not found or not authorized to delete this resume");
                $response->getBody()->write(json_encode($apiResponse->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $resume->delete();

            $apiResponse = new ApiResponse(200, null, "Resume deleted successfully");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [], $e->getTraceAsString());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
