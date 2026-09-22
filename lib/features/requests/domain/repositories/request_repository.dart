import 'dart:io';
import '../entities/comment_entity.dart';
import '../entities/request_entity.dart';

abstract class RequestRepository {
  Future<RequestEntity> createRequest({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  });

  Future<List<RequestEntity>> getRequests({String? statusFilter});
  Future<RequestEntity> getRequestDetail(int id);
  Future<RequestEntity> updateRequest(int id, Map<String, dynamic> data);
  Future<void> cancelRequest(int id);
  Future<CommentEntity> addComment(int requestId, String content);
}
