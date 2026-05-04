import { DataTypes } from "sequelize";
import { sequelize } from "../db/index.js";
import User from "./user.model.js";

const Resume = sequelize.define("Resume", {
  id: {
    type: DataTypes.INTEGER,
    autoIncrement: true,
    primaryKey: true,
  },
  firstName: { type: DataTypes.STRING, defaultValue: "" },
  lastName: { type: DataTypes.STRING, defaultValue: "" },
  email: { type: DataTypes.STRING, defaultValue: "" },
  title: { type: DataTypes.STRING, allowNull: false },
  summary: { type: DataTypes.TEXT, defaultValue: "" },
  jobTitle: { type: DataTypes.STRING, defaultValue: "" },
  phone: { type: DataTypes.STRING, defaultValue: "" },
  address: { type: DataTypes.STRING, defaultValue: "" },
  themeColor: { type: DataTypes.STRING, allowNull: false },
  experience: {
    type: DataTypes.JSON,
    defaultValue: [],
  },
  education: {
    type: DataTypes.JSON,
    defaultValue: [],
  },
  skills: {
    type: DataTypes.JSON,
    defaultValue: [],
  },
  projects: {
    type: DataTypes.JSON,
    defaultValue: [],
  },
  // Reverting to userId to match Sequelize defaults but keeping it consistent
  userId: {
    type: DataTypes.INTEGER,
    allowNull: false,
    references: {
      model: User,
      key: 'id'
    }
  }
}, {
  timestamps: true,
});

// Define associations
User.hasMany(Resume, { foreignKey: 'userId', onDelete: 'CASCADE' });
Resume.belongsTo(User, { foreignKey: 'userId' });

// Add _id and user for frontend compatibility
Resume.prototype.toJSON = function () {
  const values = { ...this.get() };
  values._id = values.id;
  values.user = values.userId;
  return values;
};

export default Resume;
