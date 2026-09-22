abstract class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;
}

class ServerException extends ApiException {
  ServerException([super.message = 'Error interno del servidor']);
}

class UnauthorizedException extends ApiException {
  UnauthorizedException([super.message = 'Sesión expirada o credenciales inválidas']);
}

class ForbiddenException extends ApiException {
  ForbiddenException([super.message = 'No tiene permisos para realizar esta acción']);
}

class BadRequestException extends ApiException {
  BadRequestException([super.message = 'Solicitud incorrecta']);
}

class ValidationException extends ApiException {
  final Map<String, dynamic>? errors;
  ValidationException(super.message, {this.errors});
}

class NetworkException extends ApiException {
  NetworkException([super.message = 'Sin conexión a internet. Verifique su red']);
}

class CacheException extends ApiException {
  CacheException([super.message = 'Error al acceder al almacenamiento local']);
}
