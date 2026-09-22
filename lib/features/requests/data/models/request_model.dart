import '../../domain/entities/request_entity.dart';
import 'comment_model.dart';
import 'evidence_model.dart';
import 'status_history_model.dart';

class RequestModel extends RequestEntity {
  const RequestModel({
    required super.id,
    required super.requestType,
    required super.title,
    required super.location,
    required super.description,
    required super.priority,
    required super.status,
    super.assignedTechnician,
    super.studentName,
    required super.createdAt,
    super.updatedAt,
    super.evidences = const [],
    super.comments = const [],
    super.statusHistory = const [],
  });

  factory RequestModel.fromJson(Map<String, dynamic> json) {
    // Manejo de evidencias
    var evidencesList = <EvidenceModel>[];
    if (json['evidences'] is List) {
      evidencesList = (json['evidences'] as List)
          .map((e) => EvidenceModel.fromJson(e as Map<String, dynamic>))
          .toList();
    } else if (json['evidence_url'] != null) {
      evidencesList = [
        EvidenceModel(
          id: 1,
          fileUrl: json['evidence_url']?.toString() ?? '',
          fileName: 'evidencia_adjunta',
          createdAt: json['created_at']?.toString() ?? '',
        ),
      ];
    }

    // Manejo de comentarios
    var commentsList = <CommentModel>[];
    if (json['comments'] is List) {
      commentsList = (json['comments'] as List)
          .map((c) => CommentModel.fromJson(c as Map<String, dynamic>))
          .toList();
    }

    // Manejo de historial de estados
    var historyList = <StatusHistoryModel>[];
    if (json['status_history'] is List) {
      historyList = (json['status_history'] as List)
          .map((h) => StatusHistoryModel.fromJson(h as Map<String, dynamic>))
          .toList();
    }

    return RequestModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      requestType: json['request_type']?.toString() ?? json['requestType']?.toString() ?? 'Mantenimiento',
      title: json['title']?.toString() ?? '',
      location: json['location']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      priority: json['priority']?.toString() ?? 'Media',
      status: json['status']?.toString() ?? 'Pendiente',
      assignedTechnician: json['assigned_technician']?.toString() ?? json['assignedTechnician']?.toString(),
      studentName: json['student_name']?.toString() ?? json['studentName']?.toString(),
      createdAt: json['created_at']?.toString() ?? json['createdAt']?.toString() ?? '',
      updatedAt: json['updated_at']?.toString() ?? json['updatedAt']?.toString(),
      evidences: evidencesList,
      comments: commentsList,
      statusHistory: historyList,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'request_type': requestType,
      'title': title,
      'location': location,
      'description': description,
      'priority': priority,
      'status': status,
      'assigned_technician': assignedTechnician,
      'student_name': studentName,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'evidences': (evidences as List<EvidenceModel>).map((e) => e.toJson()).toList(),
      'comments': (comments as List<CommentModel>).map((c) => c.toJson()).toList(),
      'status_history': (statusHistory as List<StatusHistoryModel>).map((h) => h.toJson()).toList(),
    };
  }
}
