<img src="./readme/card-titles/title1.svg"/>

<br><br>

## License

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

<br><br>

<!-- project overview -->
<img src="./readme/card-titles/title2.svg"/>

> 
> VitaLens is a comprehensive health intelligence platform designed to be the bridge between raw clinical data and meaningful understanding. The main goal of VitaLens is to automate the processing of patient medical records through smart workflows, and then leverage Machine Learning to offer risk predictions and data-driven insights.
> 

<br><br>

<!-- System Design -->
<img src="./readme/card-titles/title3.svg"/>

### System Design
<img src="./readme/system-design/VitaLens-system-design.png"/>

### Entity Relationship Diagram

[Eraser](https://app.eraser.io/workspace/AUjaUZ2UjylcsMpbdjEF)

<img src="./readme/system-design/VitaLens-erd.png"/>

### n8n
<img src="./readme/system-design/n8n-workflow.png"/>

<br><br>

<!-- Project Highlights -->
<img src="./readme/card-titles/title4.svg"/>

### Interesting Features

- Intelligent Medical Data Extraction: Uses OCR and NLP pipelines to automatically parse unstructured medical documents (PDFs, images) and daily habit logs, standardizing them into structured health metrics for analysis.
- Predictive Disease Risk Modeling: A custom-trained machine learning engine that processes your historical health data to forecast risk probabilities for critical conditions like Diabetes, CKD, and Cardiovascular disease.
- RAG-Powered Health Assistant: An interactive AI agent utilizing Retrieval-Augmented Generation to provide context-aware answers solely based on your personal medical history and uploaded reports.
- Automated Clinic Integration (n8n): A seamless background workflow connecting Google Drive to the platform, allowing clinics to batch-upload patient records for instant, automated risk assessment and processing.

<br>

<img src="./readme/features/VitaLens-features.png"/>

<br>

### Machine Learning Development

- Dataset sourced from NHANES (National Health and Nutrition Examination Survey) with 19,431 real patient health records spanning demographics, vitals, lab results, and lifestyle factors.
- Features 31 comprehensive health metrics including age, gender, BMI, blood pressure readings, glucose levels, HbA1c, cholesterol markers, kidney function tests, and physical activity measures.
- Data preprocessing included median imputation for missing numeric values and stratified train/validation/test splitting (60/20/20) to maintain disease prevalence distribution.

![Dataset](./readme/ml/dataset_sample.png)

<br>

- Trained four separate XGBoost binary classifiers to predict risk for Type 2 Diabetes, Heart Disease, Hypertension, and Chronic Kidney Disease.
- Implemented class imbalance handling using scale_pos_weight to account for varying disease prevalence in the dataset, ensuring balanced learning across positive and negative cases.
- Used early stopping (20 rounds patience) during training to prevent overfitting and optimize model generalization on unseen data.

| Training Output | Model Performance |
| :---: | :---: |
| ![Training](./readme/ml/training_output.png) | ![Performance](./readme/ml/model_performance.png) |

<br>

- Each model uses disease-specific features selected based on medical relevance (e.g., HbA1c and glucose for diabetes, LDL and HDL cholesterol for heart disease).

![Features](./readme/ml/feature_importance.png)


<br><br>

<!-- Demo -->
<img src="./readme/card-titles/title5.svg"/>


### User Screens

| Landing | Login |
| --------------------------------------- | ------------------------------------- |
| ![Landing](./readme/demo/landing.gif) | ![Login](./readme/demo/login.png) |

| Register | Dashboard (Empty) |
| --------------------------------------- | ------------------------------------- |
| ![Register](./readme/demo/register.gif) | ![Dashboard Empty](./readme/demo/dashboard-empty.png) |

| Dashboard (Full) | Documents |
| --------------------------------------- | ------------------------------------- |
| ![Dashboard Full](./readme/demo/dashboard-full.gif) | ![Documents](./readme/demo/document-upload.gif) |

| Daily Logs | AI Chat |
| --------------------------------------- | ------------------------------------- |
| ![Daily Logs](./readme/demo/daily-log.gif) | ![AI Chat](./readme/demo/AI-chat.gif) |

| Risk Details | Profile |
| --------------------------------------- | ------------------------------------- |
| ![Risk Details](./readme/demo/risk-details.gif) | ![Profile](./readme/demo/profile.png) |


<br><br>

<!-- Development & Testing -->
<img src="./readme/card-titles/title6.svg"/>

### Development Example


| Services                            | Validation                       | Controller                        |
| --------------------------------------- | ------------------------------------- | ------------------------------------- |
| ![Service](./readme/development/Service_ex.png) | ![Validation](./readme/development/Validation_ex.png) | ![Controller](./readme/development/Controller_ex.png) |


<br>

### CI $ Testing

| CI | Tests |
| --------------------------------------- | ------------------------------------- |
| ![CI](./readme/tests/CI.png) | ![Tests](./readme/tests/Test_ex.png) |

<br><br>

<!-- Deployment -->
<img src="./readme/card-titles/title7.svg"/>

### Add Title Here

- Description here.


| Postman API 1                            | Postman API 2                       | Postman API 3                        |
| --------------------------------------- | ------------------------------------- | ------------------------------------- |
| ![Landing](./readme/demo/1440x1024.png) | ![fsdaf](./readme/demo/1440x1024.png) | ![fsdaf](./readme/demo/1440x1024.png) |

<br><br>
