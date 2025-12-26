#!/bin/bash

# Google Cloud Run Deployment Script for Ollama
# Author: Albashiro Team
# Date: 2025-12-26

set -e  # Exit on error

# Configuration
PROJECT_ID="albashiro-ai"
REGION="asia-southeast1"  # Singapore (closest to Indonesia)
SERVICE_NAME="ollama-api"
IMAGE_NAME="gcr.io/$PROJECT_ID/$SERVICE_NAME"

echo "🚀 Deploying Ollama to Google Cloud Run"
echo "========================================"
echo "Project: $PROJECT_ID"
echo "Region: $REGION"
echo "Service: $SERVICE_NAME"
echo ""

# Check if gcloud is installed
if ! command -v gcloud &> /dev/null; then
    echo "❌ Error: gcloud CLI not found. Please install Google Cloud SDK."
    echo "   Visit: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

# Check if project exists
echo "📋 Checking project..."
if ! gcloud projects describe $PROJECT_ID &> /dev/null; then
    echo "⚠️  Project $PROJECT_ID not found. Creating..."
    gcloud projects create $PROJECT_ID --name="Albashiro AI"
fi

# Set active project
gcloud config set project $PROJECT_ID

# Enable required APIs
echo "🔧 Enabling required APIs..."
gcloud services enable run.googleapis.com
gcloud services enable cloudbuild.googleapis.com
gcloud services enable containerregistry.googleapis.com

# Build Docker image
echo "🐳 Building Docker image..."
gcloud builds submit --tag $IMAGE_NAME .

# Deploy to Cloud Run
echo "☁️  Deploying to Cloud Run..."
gcloud run deploy $SERVICE_NAME \
  --image $IMAGE_NAME \
  --platform managed \
  --region $REGION \
  --memory 4Gi \
  --cpu 2 \
  --timeout 300 \
  --max-instances 3 \
  --min-instances 0 \
  --allow-unauthenticated \
  --port 11434 \
  --set-env-vars="OLLAMA_HOST=0.0.0.0:11434"

# Get service URL
echo ""
echo "✅ Deployment complete!"
echo "======================="
SERVICE_URL=$(gcloud run services describe $SERVICE_NAME \
  --region $REGION \
  --format 'value(status.url)')

echo "Service URL: $SERVICE_URL"
echo ""
echo "📝 Next steps:"
echo "1. Test the endpoint:"
echo "   curl $SERVICE_URL/api/tags"
echo ""
echo "2. Add to Vercel environment variables:"
echo "   OLLAMA_API_URL=$SERVICE_URL"
echo ""
echo "3. Add to .env.local for local testing:"
echo "   OLLAMA_API_URL=$SERVICE_URL"
echo ""
echo "🎉 Done! Your Ollama LLM is now running on Google Cloud Run."
