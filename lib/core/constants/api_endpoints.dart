class ApiEndpoints {
  // Puede ser configurado con variables de entorno o constantes de compilación
  static const String baseUrl = 'https://api.campusconnect.edu/api/v1';

  // Auth
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String profile = '/auth/me';

  // Requests
  static const String requests = '/requests';
  static String requestDetail(int id) => '/requests/$id';
  static String requestComments(int id) => '/requests/$id/comments';
}
