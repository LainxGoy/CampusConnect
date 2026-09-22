import 'package:equatable/equatable.dart';

class EvidenceEntity extends Equatable {
  final int id;
  final String fileUrl;
  final String fileName;
  final String? fileType;
  final String createdAt;

  const EvidenceEntity({
    required this.id,
    required this.fileUrl,
    required this.fileName,
    this.fileType,
    required this.createdAt,
  });

  @override
  List<Object?> get props => [id, fileUrl, fileName, fileType, createdAt];
}
