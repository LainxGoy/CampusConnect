import '../entities/request_entity.dart';
import '../repositories/request_repository.dart';

class GetRequestDetailUseCase {
  final RequestRepository _repository;
  GetRequestDetailUseCase(this._repository);

  Future<RequestEntity> execute(int id) {
    return _repository.getRequestDetail(id);
  }
}
