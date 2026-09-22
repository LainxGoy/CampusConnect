import 'package:dio/dio.dart';
import '../constants/api_endpoints.dart';
import '../errors/exceptions.dart';
import '../storage/secure_storage_service.dart';
import 'auth_interceptor.dart';

class ApiClient {
  late final Dio dio;

  ApiClient({
    required SecureStorageService storageService,
    Dio? customDio,
  }) {
    dio = customDio ??
        Dio(
          BaseOptions(
            baseUrl: ApiEndpoints.baseUrl,
            connectTimeout: const Duration(seconds: 15),
            receiveTimeout: const Duration(seconds: 15),
            sendTimeout: const Duration(seconds: 30),
          ),
        );

    if (customDio == null) {
      dio.interceptors.add(AuthInterceptor(storageService));
    }
  }

  ApiException handleDioError(DioException e) {
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout ||
        e.type == DioExceptionType.sendTimeout ||
        e.type == DioExceptionType.connectionError) {
      return NetworkException('Tiempo de espera agotado. Verifique su conexión');
    }

    final response = e.response;
    if (response == null) {
      return NetworkException('No se pudo establecer conexión con el servidor');
    }

    final dynamic data = response.data;
    String message = 'Ocurrió un error inesperado';
    Map<String, dynamic>? validationErrors;

    if (data is Map<String, dynamic>) {
      message = data['message'] ?? message;
      if (data['errors'] is Map<String, dynamic>) {
        validationErrors = data['errors'] as Map<String, dynamic>;
      }
    }

    switch (response.statusCode) {
      case 400:
        return BadRequestException(message);
      case 401:
        return UnauthorizedException(message.isNotEmpty ? message : 'Credenciales inválidas');
      case 403:
        return ForbiddenException(message.isNotEmpty ? message : 'Acceso denegado');
      case 422:
        return ValidationException(message, errors: validationErrors);
      case 500:
      case 502:
      case 503:
        return ServerException('Error interno del servidor ($message)');
      default:
        return ServerException(message);
    }
  }
}
