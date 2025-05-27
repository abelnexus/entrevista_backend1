import axios from "axios";
import router from "@/router";

const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  baseURL: "http://localhost/api/",
  // timeout: 1000,
  // headers: {'X-Custom-Header': 'foobar'}
});

// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
axiosIns.interceptors.request.use((config) => {
  // Retrieve token from localStorage
  const token = localStorage.getItem("accessToken");
  // If token is found
  if (token) {
    // Get request headers and if headers is undefined assign blank object
    config.headers = config.headers || {};

    // Set authorization header
    // ℹ️ JSON.parse will convert token to string
    config.headers.Authorization = token ? `Bearer ${token}` : "";
  }
  // Return modified config
  return config;
});

// ℹ️ Add response interceptor to handle 401 response
axiosIns.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    console.log("Error capturado:", error); // Revisa qué información trae el error
    if (error.response) {
      if (error.response.status === 401) {
        console.log("error 401");
        localStorage.removeItem("userData");
        localStorage.removeItem("accessToken");
        router.push("/login");
      }
    } else {
      console.log("No se recibió una respuesta del servidor");
    }
    return Promise.reject(error);
  }
);
export default axiosIns;
