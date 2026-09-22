import '../entities/request_entity.dart';
import '../repositories/request_repository.dart';

class UpdateRequestUseCase {
  final RequestRepository _repository;
  UpdateRequestUseCase(this._repository);

  Future<RequestEntity> execute(int id, Map<String, dynamic> data) {
    return _repository.updateRequest(id, data);
  }
}
