import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/validators.dart';
import '../../domain/entities/request_entity.dart';
import '../providers/request_provider.dart';

class EditRequestScreen extends StatefulWidget {
  final RequestEntity request;

  const EditRequestScreen({super.key, required this.request});

  @override
  State<EditRequestScreen> createState() => _EditRequestScreenState();
}

class _EditRequestScreenState extends State<EditRequestScreen> {
  final _formKey = GlobalKey<FormState>();

  late final TextEditingController _titleController;
  late final TextEditingController _locationController;
  late final TextEditingController _descriptionController;

  late String _selectedType;
  late String _selectedPriority;

  final List<String> _requestTypes = [
    'Mantenimiento',
    'Soporte Tecnológico',
    'Infraestructura',
    'Equipamiento',
  ];

  final List<String> _priorities = ['Baja', 'Media', 'Alta', 'Crítica'];

  @override
  void initState() {
    super.initState();
    _titleController = TextEditingController(text: widget.request.title);
    _locationController = TextEditingController(text: widget.request.location);
    _descriptionController = TextEditingController(text: widget.request.description);
    _selectedType = widget.request.requestType;
    _selectedPriority = widget.request.priority;
  }

  @override
  void dispose() {
    _titleController.dispose();
    _locationController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _handleUpdate() async {
    if (!_formKey.currentState!.validate()) return;

    final provider = context.read<RequestProvider>();
    final updateData = {
      'request_type': _selectedType,
      'title': _titleController.text.trim(),
      'location': _locationController.text.trim(),
      'description': _descriptionController.text.trim(),
      'priority': _selectedPriority,
    };

    final success = await provider.updateRequest(widget.request.id, updateData);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Solicitud actualizada correctamente'),
          backgroundColor: AppColors.statusResolved,
        ),
      );
      Navigator.of(context).pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.errorMessage ?? 'Error al actualizar'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = context.watch<RequestProvider>().isLoading;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Editar Solicitud'),
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
                initialValue: _selectedType,
                decoration: const InputDecoration(
                  labelText: 'Tipo de Solicitud *',
                  prefixIcon: Icon(Icons.category_outlined),
                ),
                items: _requestTypes.map((type) {
                  return DropdownMenuItem(value: type, child: Text(type));
                }).toList(),
                onChanged: (value) {
                  if (value != null) setState(() => _selectedType = value);
                },
                validator: (value) => value == null ? 'Seleccione el tipo' : null,
              ),
              const SizedBox(height: 16),

              // Título
              TextFormField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Título *',
                  prefixIcon: Icon(Icons.title),
                ),
                validator: (value) => Validators.minLength(value, 5, 'Título'),
              ),
              const SizedBox(height: 16),

              // Ubicación
              TextFormField(
                controller: _locationController,
                decoration: const InputDecoration(
                  labelText: 'Ubicación *',
                  prefixIcon: Icon(Icons.place_outlined),
                ),
                validator: (value) => Validators.requiredField(value, 'Ubicación'),
              ),
              const SizedBox(height: 16),

              // Prioridad
              DropdownButtonFormField<String>(
                initialValue: _selectedPriority,
                decoration: const InputDecoration(
                  labelText: 'Prioridad Estimada',
                  prefixIcon: Icon(Icons.flag_outlined),
                ),
                items: _priorities.map((p) {
                  return DropdownMenuItem(value: p, child: Text(p));
                }).toList(),
                onChanged: (value) {
                  if (value != null) setState(() => _selectedPriority = value);
                },
              ),
              const SizedBox(height: 16),

              // Descripción
              TextFormField(
                controller: _descriptionController,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Descripción *',
                  alignLabelWithHint: true,
                  prefixIcon: Icon(Icons.description_outlined),
                ),
                validator: (value) => Validators.minLength(value, 15, 'Descripción'),
              ),
              const SizedBox(height: 28),

              // Botón Guardar Cambios
              ElevatedButton(
                onPressed: isLoading ? null : _handleUpdate,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'GUARDAR CAMBIOS',
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
