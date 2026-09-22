import 'package:equatable/equatable.dart';
import 'comment_entity.dart';
import 'evidence_entity.dart';
import 'status_history_entity.dart';

class RequestEntity extends Equatable {
  final int id;
  final String requestType; // Mantenimiento, Soporte Tecnológico, Infraestructura, Equipamiento
  final String title;
  final String location;
  final String description;
  final String priority; // Baja, Media, Alta, Crítica
  final String status; // Pendiente, En Proceso, Resuelto, Cerrado, Cancelado
  final String? assignedTechnician;
  final String? studentName;
  final String createdAt;
  final String? updatedAt;
  final List<EvidenceEntity> evidences;
  final List<CommentEntity> comments;
  final List<StatusHistoryEntity> statusHistory;

  const RequestEntity({
    required this.id,
    required this.requestType,
    required this.title,
    required this.location,
    required this.description,
    required this.priority,
    required this.status,
    this.assignedTechnician,
    this.studentName,
    required this.createdAt,
    this.updatedAt,
    this.evidences = const [],
    this.comments = const [],
    this.statusHistory = const [],
  });

  bool get isEditable => status.toLowerCase() == 'pendiente';
  bool get isCancellable => status.toLowerCase() == 'pendiente' && (assignedTechnician == null || assignedTechnician!.isEmpty);

  @override
  List<Object?> get props => [
        id,
        requestType,
        title,
        location,
        description,
        priority,
        status,
        assignedTechnician,
        studentName,
        createdAt,
        updatedAt,
        evidences,
        comments,
        statusHistory,
      ];
}
