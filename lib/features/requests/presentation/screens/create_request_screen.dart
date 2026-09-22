import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/validators.dart';
import '../providers/request_provider.dart';

class CreateRequestScreen extends StatefulWidget {
  const CreateRequestScreen({super.key});

  @override
  State<CreateRequestScreen> createState() => _CreateRequestScreenState();
}

class _CreateRequestScreenState extends State<CreateRequestScreen> {
  final _formKey = GlobalKey<FormState>();

  final _titleController = TextEditingController();
  final _locationController = TextEditingController();
  final _descriptionController = TextEditingController();

  String? _selectedType;
  String _selectedPriority = 'Media';
  File? _evidenceImage;

  final List<String> _requestTypes = [
    'Mantenimiento',
    'Soporte Tecnológico',
    'Infraestructura',
    'Equipamiento',
  ];

  final List<String> _priorities = ['Baja', 'Media', 'Alta', 'Crítica'];
  final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _titleController.dispose();
    _locationController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? pickedFile = await _picker.pickImage(
        source: source,
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 80,
      );

      if (pickedFile != null) {
        setState(() {
          _evidenceImage = File(pickedFile.path);
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error al adjuntar archivo: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  void _showImageSourceDialog() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => SafeArea(
        child: Wrap(
          children: [
            ListTile(
              leading: const Icon(Icons.camera_alt, color: AppColors.primary),
              title: const Text('Tomar Fotografía con la Cámara'),
              onTap: () {
                Navigator.of(ctx).pop();
                _pickImage(ImageSource.camera);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library, color: AppColors.secondary),
              title: const Text('Elegir de la Galería'),
              onTap: () {
                Navigator.of(ctx).pop();
                _pickImage(ImageSource.gallery);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    final provider = context.read<RequestProvider>();

    final success = await provider.createRequest(
      requestType: _selectedType!,
      title: _titleController.text.trim(),
      location: _locationController.text.trim(),
      description: _descriptionController.text.trim(),
      priority: _selectedPriority,
      evidenceFile: _evidenceImage,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('¡Solicitud registrada exitosamente!'),
          backgroundColor: AppColors.statusResolved,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.of(context).pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.errorMessage ?? 'Ocurrió un error al registrar la solicitud'),
          backgroundColor: AppColors.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = context.watch<RequestProvider>().isLoading;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Nueva Solicitud'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Tipo de Solicitud
              DropdownButtonFormField<String>(
                key: const Key('dropdown_request_type'),
                initialValue: _selectedType,
                decoration: const InputDecoration(
                  labelText: 'Tipo de Solicitud *',
                  prefixIcon: Icon(Icons.category_outlined),
                ),
                items: _requestTypes.map((type) {
                  return DropdownMenuItem(value: type, child: Text(type));
                }).toList(),
                onChanged: (value) => setState(() => _selectedType = value),
                validator: (value) =>
                    value == null ? 'Seleccione el tipo de solicitud' : null,
              ),
              const SizedBox(height: 16),

              // Título
              TextFormField(
                key: const Key('input_title'),
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Título de la incidencia *',
                  hintText: 'Ej: Proyector dañado o luminaria parpadeando',
                  prefixIcon: Icon(Icons.title),
                ),
                validator: (value) => Validators.minLength(value, 5, 'Título'),
              ),
              const SizedBox(height: 16),

              // Ubicación
              TextFormField(
                key: const Key('input_location'),
                controller: _locationController,
                decoration: const InputDecoration(
                  labelText: 'Ubicación / Pabellón / Aula *',
                  hintText: 'Ej: Pabellón B - Aula 302',
                  prefixIcon: Icon(Icons.place_outlined),
                ),
                validator: (value) => Validators.requiredField(value, 'Ubicación'),
              ),
              const SizedBox(height: 16),

              // Prioridad
              DropdownButtonFormField<String>(
                key: const Key('dropdown_priority'),
                initialValue: _selectedPriority,
                decoration: const InputDecoration(
                  labelText: 'Prioridad Estimada',
                  prefixIcon: Icon(Icons.flag_outlined),
                ),
                items: _priorities.map((p) {
                  return DropdownMenuItem(value: p, child: Text(p));
                }).toList(),
                onChanged: (value) => setState(() => _selectedPriority = value!),
              ),
              const SizedBox(height: 16),

              // Descripción Detallada
              TextFormField(
                key: const Key('input_description'),
                controller: _descriptionController,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Descripción Detallada *',
                  hintText: 'Explica con detalle el problema encontrado para facilitar la atención del técnico...',
                  alignLabelWithHint: true,
                  prefixIcon: Icon(Icons.description_outlined),
                ),
                validator: (value) => Validators.minLength(value, 15, 'Descripción'),
              ),
              const SizedBox(height: 20),

              // Evidencia Fotográfica
              const Text(
                'Evidencia Fotográfica (Opcional)',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              ),
              const SizedBox(height: 8),

              if (_evidenceImage != null)
                Stack(
                  alignment: Alignment.topRight,
                  children: [
                    Container(
                      height: 180,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: AppColors.border),
                        image: DecorationImage(
                          image: FileImage(_evidenceImage!),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      right: 8,
                      child: CircleAvatar(
                        backgroundColor: Colors.black54,
                        radius: 18,
                        child: IconButton(
                          icon: const Icon(Icons.close, color: Colors.white, size: 18),
                          onPressed: () => setState(() => _evidenceImage = null),
                        ),
                      ),
                    ),
                  ],
                )
              else
                OutlinedButton.icon(
                  key: const Key('btn_add_evidence'),
                  onPressed: _showImageSourceDialog,
                  icon: const Icon(Icons.add_a_photo_outlined),
                  label: const Text('Adjuntar Evidencia (Cámara / Galería)'),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: AppColors.primary),
                  ),
                ),
              const SizedBox(height: 28),

              // Botón Submit
              ElevatedButton(
                key: const Key('btn_submit_request'),
                onPressed: isLoading ? null : _submitForm,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: isLoading
                    ? const SizedBox(
                        height: 22,
                        width: 22,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'REGISTRAR SOLICITUD',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
