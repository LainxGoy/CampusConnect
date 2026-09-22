import '../entities/request_entity.dart';
import '../repositories/request_repository.dart';

class GetRequestsUseCase {
  final RequestRepository _repository;
  GetRequestsUseCase(this._repository);

  Future<List<RequestEntity>> execute({String? statusFilter}) {
    return _repository.getRequests(statusFilter: statusFilter);
  }
}
