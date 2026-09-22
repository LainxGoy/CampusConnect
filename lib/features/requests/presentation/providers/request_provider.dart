import 'dart:io';
import 'package:flutter/material.dart';
import '../../../../core/errors/exceptions.dart';
import '../../domain/entities/request_entity.dart';
import '../../domain/usecases/add_comment_usecase.dart';
import '../../domain/usecases/cancel_request_usecase.dart';
import '../../domain/usecases/create_request_usecase.dart';
import '../../domain/usecases/get_request_detail_usecase.dart';
import '../../domain/usecases/get_requests_usecase.dart';
import '../../domain/usecases/update_request_usecase.dart';

class RequestProvider extends ChangeNotifier {
  final CreateRequestUseCase _createRequestUseCase;
  final GetRequestsUseCase _getRequestsUseCase;
  final GetRequestDetailUseCase _getRequestDetailUseCase;
  final UpdateRequestUseCase _updateRequestUseCase;
  final CancelRequestUseCase _cancelRequestUseCase;
  final AddCommentUseCase _addCommentUseCase;

  List<RequestEntity> _requests = [];
  RequestEntity? _selectedRequest;
  String _currentFilter = 'Todos';
  bool _isLoading = false;
  bool _isDetailLoading = false;
  String? _errorMessage;

  RequestProvider({
    required CreateRequestUseCase createRequestUseCase,
    required GetRequestsUseCase getRequestsUseCase,
    required GetRequestDetailUseCase getRequestDetailUseCase,
    required UpdateRequestUseCase updateRequestUseCase,
    required CancelRequestUseCase cancelRequestUseCase,
    required AddCommentUseCase addCommentUseCase,
  })  : _createRequestUseCase = createRequestUseCase,
        _getRequestsUseCase = getRequestsUseCase,
        _getRequestDetailUseCase = getRequestDetailUseCase,
        _updateRequestUseCase = updateRequestUseCase,
        _cancelRequestUseCase = cancelRequestUseCase,
        _addCommentUseCase = addCommentUseCase;

  List<RequestEntity> get requests => _requests;
  RequestEntity? get selectedRequest => _selectedRequest;
  String get currentFilter => _currentFilter;
  bool get isLoading => _isLoading;
  bool get isDetailLoading => _isDetailLoading;
  String? get errorMessage => _errorMessage;

  void setFilter(String filter) {
    if (_currentFilter != filter) {
      _currentFilter = filter;
      fetchRequests();
    }
  }

  Future<void> fetchRequests() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final status = _currentFilter == 'Todos' ? null : _currentFilter;
      _requests = await _getRequestsUseCase.execute(statusFilter: status);
      _isLoading = false;
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = 'Error al cargar las solicitudes';
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchRequestDetail(int id) async {
    _isDetailLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _selectedRequest = await _getRequestDetailUseCase.execute(id);
      _isDetailLoading = false;
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isDetailLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = 'Error al cargar el detalle de la solicitud';
      _isDetailLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createRequest({
    required String requestType,
    required String title,
    required String location,
    required String description,
    required String priority,
    File? evidenceFile,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final newRequest = await _createRequestUseCase.execute(
        requestType: requestType,
        title: title,
        location: location,
        description: description,
        priority: priority,
        evidenceFile: evidenceFile,
      );
      _requests.insert(0, newRequest);
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Error al registrar la solicitud';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateRequest(int id, Map<String, dynamic> data) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _updateRequestUseCase.execute(id, data);
      final index = _requests.indexWhere((r) => r.id == id);
      if (index != -1) {
        _requests[index] = updated;
      }
      if (_selectedRequest?.id == id) {
        _selectedRequest = updated;
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Error al actualizar la solicitud';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> cancelRequest(int id) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _cancelRequestUseCase.execute(id);
      _requests.removeWhere((r) => r.id == id);
      if (_selectedRequest?.id == id) {
        _selectedRequest = null;
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Error al cancelar la solicitud';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> addComment(int requestId, String content) async {
    try {
      final comment = await _addCommentUseCase.execute(requestId, content);
      if (_selectedRequest != null && _selectedRequest!.id == requestId) {
        final updatedComments = List.of(_selectedRequest!.comments)..add(comment);
        _selectedRequest = RequestEntity(
          id: _selectedRequest!.id,
          requestType: _selectedRequest!.requestType,
          title: _selectedRequest!.title,
          location: _selectedRequest!.location,
          description: _selectedRequest!.description,
          priority: _selectedRequest!.priority,
          status: _selectedRequest!.status,
          assignedTechnician: _selectedRequest!.assignedTechnician,
          studentName: _selectedRequest!.studentName,
          createdAt: _selectedRequest!.createdAt,
          updatedAt: _selectedRequest!.updatedAt,
          evidences: _selectedRequest!.evidences,
          comments: updatedComments,
          statusHistory: _selectedRequest!.statusHistory,
        );
        notifyListeners();
      }
      return true;
    } catch (e) {
      _errorMessage = 'Error al enviar el comentario';
      notifyListeners();
      return false;
    }
  }
}
