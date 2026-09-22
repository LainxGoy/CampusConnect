import 'dart:io';
import 'package:dio/dio.dart';
import 'package:http_parser/http_parser.dart';
import 'package:path/path.dart' as p;
import '../../../../core/constants/api_endpoints.dart';
import '../../../../core/network/api_client.dart';
import '../models/comment_model.dart';
import '../models/request_model.dart';

abstract class RequestRemoteDataSource {
  Future<RequestModel> createRequest({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  });

  Future<List<RequestModel>> getRequests({String? statusFilter});
  Future<RequestModel> getRequestDetail(int id);
  Future<RequestModel> updateRequest(int id, Map<String, dynamic> data);
  Future<void> cancelRequest(int id);
  Future<CommentModel> addComment(int requestId, String content);
}

class RequestRemoteDataSourceImpl implements RequestRemoteDataSource {
  final ApiClient _apiClient;

  RequestRemoteDataSourceImpl(this._apiClient);

  @override
  Future<RequestModel> createRequest({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  }) async {
    try {
      final Map<String, dynamic> formMap = {
        'request_type': requestType,
        'title': title,
        'location': location,
        'description': description,
        'priority': priority,
      };

      if (evidenceFile != null) {
        final fileName = p.basename(evidenceFile.path);
        final extension = p.extension(evidenceFile.path).replaceAll('.', '');

        formMap['evidence'] = await MultipartFile.fromFile(
          evidenceFile.path,
          filename: fileName,
          contentType: MediaType('image', extension.isEmpty ? 'jpeg' : extension),
        );
      }

      final formData = FormData.fromMap(formMap);

      final response = await _apiClient.dio.post(
        ApiEndpoints.requests,
        data: formData,
        options: Options(
          headers: {'Content-Type': 'multipart/form-data'},
        ),
      );

      final data = response.data['data'] ?? response.data;
      return RequestModel.fromJson(data);
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }

  @override
  Future<List<RequestModel>> getRequests({String? statusFilter}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (statusFilter != null && statusFilter.isNotEmpty && statusFilter.toLowerCase() != 'todos') {
        queryParams['status'] = statusFilter;
      }

      final response = await _apiClient.dio.get(
        ApiEndpoints.requests,
        queryParameters: queryParams,
      );

      final dynamic raw = response.data['data'] ?? response.data;
      if (raw is List) {
        return raw.map((json) => RequestModel.fromJson(json as Map<String, dynamic>)).toList();
      }
      return [];
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }

  @override
  Future<RequestModel> getRequestDetail(int id) async {
    try {
      final response = await _apiClient.dio.get(ApiEndpoints.requestDetail(id));
      final data = response.data['data'] ?? response.data;
      return RequestModel.fromJson(data);
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }

  @override
  Future<RequestModel> updateRequest(int id, Map<String, dynamic> data) async {
    try {
      final response = await _apiClient.dio.put(
        ApiEndpoints.requestDetail(id),
        data: data,
      );
      final resData = response.data['data'] ?? response.data;
      return RequestModel.fromJson(resData);
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }

  @override
  Future<void> cancelRequest(int id) async {
    try {
      await _apiClient.dio.delete(ApiEndpoints.requestDetail(id));
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }

  @override
  Future<CommentModel> addComment(int requestId, String content) async {
    try {
      final response = await _apiClient.dio.post(
        ApiEndpoints.requestComments(requestId),
        data: {'content': content},
      );
      final data = response.data['data'] ?? response.data;
      return CommentModel.fromJson(data);
    } on DioException catch (e) {
      throw _apiClient.handleDioError(e);
    }
  }
}
