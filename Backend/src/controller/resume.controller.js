import { ApiError } from "../utils/ApiError.js";
import { ApiResponse } from "../utils/ApiResponse.js";
import Resume from "../models/resume.model.js";

const start = async (req, res) => {
  return res
    .status(200)
    .json(new ApiResponse(200, null, "Welcome to Resume Builder API"));
};

const createResume = async (req, res) => {
  const { title, themeColor } = req.body;

  if (!title || !themeColor) {
    return res
      .status(400)
      .json(new ApiError(400, "Title and themeColor are required."));
  }

  try {
    const resume = await Resume.create({
      title,
      themeColor,
      userId: req.user.id,
      firstName: "",
      lastName: "",
      email: "",
      summary: "",
      jobTitle: "",
      phone: "",
      address: "",
      experience: [],
      education: [],
      skills: [],
      projects: [],
    });

    return res
      .status(201)
      .json(new ApiResponse(201, { resume }, "Resume created successfully"));
  } catch (error) {
    console.error("Error creating resume:", error);
    return res
      .status(500)
      .json(
        new ApiError(500, "Internal Server Error", [error.message], error.stack)
      );
  }
};

const getALLResume = async (req, res) => {
  try {
    const resumes = await Resume.findAll({ where: { userId: req.user.id } });
    return res
      .status(200)
      .json(new ApiResponse(200, resumes, "Resumes fetched successfully"));
  } catch (error) {
    console.error("Error fetching resumes:", error);
    return res
      .status(500)
      .json(new ApiError(500, "Internal Server Error", [], error.stack));
  }
};

const getResume = async (req, res) => {
  try {
    const { id } = req.query;

    if (!id || id === 'undefined') {
      return res.status(400).json(new ApiError(400, "Valid Resume ID is required."));
    }

    const resume = await Resume.findByPk(id);

    if (!resume) {
      return res.status(404).json(new ApiError(404, "Resume not found."));
    }

    if (resume.userId !== req.user.id) {
      return res
        .status(403)
        .json(
          new ApiError(403, "You are not authorized to access this resume.")
        );
    }

    return res
      .status(200)
      .json(new ApiResponse(200, resume, "Resume fetched successfully"));
  } catch (error) {
    console.error("Error fetching resume:", error);
    return res
      .status(500)
      .json(new ApiError(500, "Internal Server Error", [], error.stack));
  }
};

const updateResume = async (req, res) => {
  const id = req.query.id;

  if (!id || id === 'undefined') {
    return res.status(400).json(new ApiError(400, "Valid Resume ID is required. Check if the URL has a valid ID."));
  }

  try {
    // Clean up req.body to remove fields that shouldn't be updated directly
    const updateData = { ...req.body };
    delete updateData.id;
    delete updateData._id;
    delete updateData.userId;
    delete updateData.user;
    delete updateData.createdAt;
    delete updateData.updatedAt;

    const [updatedRowsCount] = await Resume.update(updateData, {
      where: { id: id, userId: req.user.id }
    });

    if (updatedRowsCount === 0) {
      return res
        .status(404)
        .json(new ApiResponse(404, null, "Resume not found or unauthorized"));
    }

    const updatedResume = await Resume.findByPk(id);

    return res
      .status(200)
      .json(new ApiResponse(200, updatedResume, "Resume updated successfully"));
  } catch (error) {
    console.error("Error updating resume:", error);
    return res
      .status(500)
      .json(
        new ApiError(500, "Internal Server Error", [error.message], error.stack)
      );
  }
};

const removeResume = async (req, res) => {
  const id = req.query.id;

  if (!id || id === 'undefined') {
    return res.status(400).json(new ApiError(400, "Valid Resume ID is required."));
  }

  try {
    const deletedRowsCount = await Resume.destroy({
      where: { id: id, userId: req.user.id }
    });

    if (deletedRowsCount === 0) {
      return res
        .status(404)
        .json(
          new ApiResponse(
            404,
            null,
            "Resume not found or not authorized to delete this resume"
          )
        );
    }

    return res
      .status(200)
      .json(new ApiResponse(200, null, "Resume deleted successfully"));
  } catch (error) {
    console.error("Error while deleting resume:", error);
    return res
      .status(500)
      .json(new ApiResponse(500, null, "Internal Server Error"));
  }
};

export {
  start,
  createResume,
  getALLResume,
  getResume,
  updateResume,
  removeResume,
};
