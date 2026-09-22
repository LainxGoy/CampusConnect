import 'dart:io';
import '../entities/request_entity.dart';
import '../repositories/request_repository.dart';

class CreateRequestUseCase {
  final RequestRepository _repository;
  CreateRequestUseCase(this._repository);

  Future<RequestEntity> execute({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  }) {
    return _repository.createRequest(
      requestType: requestType,
      title: title,
      location: location,
      description: description,
      priority: priority,
      evidenceFile: evidenceFile,
    );
  }
}
