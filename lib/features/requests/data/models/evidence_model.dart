import '../../domain/entities/evidence_entity.dart';

class EvidenceModel extends EvidenceEntity {
  const EvidenceModel({
    required super.id,
    required super.fileUrl,
    required super.fileName,
    super.fileType,
    required super.createdAt,
  });

  factory EvidenceModel.fromJson(Map<String, dynamic> json) {
    return EvidenceModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      fileUrl: json['file_url']?.toString() ?? json['fileUrl']?.toString() ?? '',
      fileName: json['file_name']?.toString() ?? json['fileName']?.toString() ?? 'evidencia',
      fileType: json['file_type']?.toString() ?? json['fileType']?.toString(),
      createdAt: json['created_at']?.toString() ?? json['createdAt']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'file_url': fileUrl,
      'file_name': fileName,
      'file_type': fileType,
      'created_at': createdAt,
    };
  }
}
