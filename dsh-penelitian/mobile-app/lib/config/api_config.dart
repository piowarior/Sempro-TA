class ApiConfig {
  // Use 10.0.2.2 for Android emulator, or localhost/127.0.0.1 for desktop/web/LAN
  static String baseUrl = 'http://127.0.0.1:8000/api';

  static void setBaseUrl(String url) {
    baseUrl = url;
  }
}
