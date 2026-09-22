import '../repositories/request_repository.dart';

class CancelRequestUseCase {
  final RequestRepository _repository;
  CancelRequestUseCase(this._repository);

  Future<void> execute(int id) {
    return _repository.cancelRequest(id);
  }
}
