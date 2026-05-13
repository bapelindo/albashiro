module.exports = {
  apps: [
    {
      name: "ollama",
      script: "ollama",
      args: "serve",
      interpreter: "none",
      env: {
        OLLAMA_HOST: "0.0.0.0",
        OLLAMA_ORIGINS: "*"
      }
    },
    {
      name: "albashiroh-node",
      script: "./server.js",
      env: {
        NODE_ENV: "production",
        PORT: 3000
      }
    }
  ]
};
