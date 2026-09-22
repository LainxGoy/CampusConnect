import 'package:equatable/equatable.dart';

abstract class Failure extends Equatable {
  final String message;
  const Failure(this.message);

  @override
  List<Object?> get props => [message];
}

class ServerFailure extends Failure {
  const ServerFailure([super.message = 'Error en el servidor institucional']);
}

class AuthFailure extends Failure {
  const AuthFailure([super.message = 'Credenciales inválidas o sesión no autorizada']);
}

class ValidationFailure extends Failure {
  final Map<String, dynamic>? errors;
  const ValidationFailure(super.message, {this.errors});

  @override
  List<Object?> get props => [message, errors];
}

class NetworkFailure extends Failure {
  const NetworkFailure([super.message = 'Error de conectividad de red']);
}

class CacheFailure extends Failure {
  const CacheFailure([super.message = 'Error en almacenamiento local']);
}
