import '../entities/comment_entity.dart';
import '../repositories/request_repository.dart';

class AddCommentUseCase {
  final RequestRepository _repository;
  AddCommentUseCase(this._repository);

  Future<CommentEntity> execute(int requestId, String content) {
    return _repository.addComment(requestId, content);
  }
}
