import 'dart:io';
import '../../domain/entities/comment_entity.dart';
import '../../domain/entities/request_entity.dart';
import '../../domain/repositories/request_repository.dart';
import '../datasources/request_remote_data_source.dart';

class RequestRepositoryImpl implements RequestRepository {
  final RequestRemoteDataSource _remoteDataSource;

  RequestRepositoryImpl(this._remoteDataSource);

  @override
  Future<RequestEntity> createRequest({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  }) {
    return _remoteDataSource.createRequest(
      requestType: requestType,
      title: title,
      location: location,
      description: description,
      priority: priority,
      evidenceFile: evidenceFile,
    );
  }

  @override
  Future<List<RequestEntity>> getRequests({String? statusFilter}) {
    return _remoteDataSource.getRequests(statusFilter: statusFilter);
  }

  @override
  Future<RequestEntity> getRequestDetail(int id) {
    return _remoteDataSource.getRequestDetail(id);
  }

  @override
  Future<RequestEntity> updateRequest(int id, Map<String, dynamic> data) {
    return _remoteDataSource.updateRequest(id, data);
  }

  @override
  Future<void> cancelRequest(int id) {
    return _remoteDataSource.cancelRequest(id);
  }

  @override
  Future<CommentEntity> addComment(int requestId, String content) {
    return _remoteDataSource.addComment(requestId, content);
  }
}
