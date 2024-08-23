import { Button, FormControl, MenuItem, Select } from '@mui/material';
import React from 'react'
import { useState } from 'react';
import TextInput from './TextInput';

const Table = ({data, columns, columnsLabel={}, editAction, deleteAction, dataId }) => {

    const [search, setSearch] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [recordsPerPage, setRecordsPerPage] = useState(5); // You can adjust the number of records per page here

    //Delete model instance through hidden form
    const handleDelete = async (id) => {

        if (window.confirm('Tem a certeza que pretende eliminar o elemento com id ' + id + '?')) {

            const form = document.createElement('form');
            form.action = route(deleteAction, id);
            form.method = 'POST';
    
            // Add hidden inputs for the method and CSRF token
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
    
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
    
            document.body.appendChild(form);
            form.submit();
        }
    };

    const matchesSearch = (elem) => {
        return columns.some((col) => {
            const value = elem[col];
            if (typeof value === 'string' || typeof value === 'number') {
                return value.toString().toLowerCase().includes(search.toLowerCase());
            }
            return false;
        });
    };

    const handlePageChange = (newPage) => {
        if (newPage > 0 && newPage <= totalPages) {
            setCurrentPage(newPage);
        }
    };

    const handleRecordsPerPageChange = (event) => {
        setRecordsPerPage(parseInt(event.target.value));
        setCurrentPage(1); // Reset to the first page whenever records per page is changed
    };

    // Calculate the indexes for slicing the data
    const indexOfLastRecord = currentPage * recordsPerPage;
    const indexOfFirstRecord = indexOfLastRecord - recordsPerPage;
    const currentData = data.filter(matchesSearch).slice(indexOfFirstRecord, indexOfLastRecord);

    const totalPages = Math.ceil(data.filter(matchesSearch).length / recordsPerPage);

    const options = [5, 10, 25]; // Options for records per page

    //console.log('data', data)
    //console.log('columnsLabel', columnsLabel)
    return (
        <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>

                        <div className='m-5'>
                            <FormControl>
                                <TextInput 
                                    type="search" 
                                    onChange={(e) => setSearch(e.target.value)} 
                                    placeholder='Search'
                                />
                            </FormControl>

                                <Select
                                    value={recordsPerPage}
                                    onChange={handleRecordsPerPageChange}
                                    sx={{ marginLeft: 5, height: 40, fontSize: 14}}
                                >
                                    {options.map((option) => (
                                        <MenuItem key={option} value={option} sx={{ fontSize: 14}}>
                                            {option} por página
                                        </MenuItem>
                                    ))}
                                </Select>
                        </div>
                        <table  className='items-center bg-transparent w-full border-collapse overflow-scroll' style={{ fontSize: 16}}>
                            <thead className='border-b-2 overflow-scroll'>
                                <tr>
                                    {columns.map((col,index) => ( 
                                        <td key={index}>{columnsLabel[col] || col}</td>
                                    ))}
                                    <td>Ações</td>
                                </tr>
                            </thead>

                            <tbody style={{ fontSize: 14}}>
                                {currentData.length === 0 ? (
                                    <tr>
                                        <td colSpan={columns.length + 1} className="text-center py-4">
                                            Sem resultados
                                        </td>
                                    </tr>
                                ) : (
                                    currentData.map((elem, i) => (
                                        <tr key={elem[dataId]}>
                                            {columns.map((col, index) => (
                                                <td key={index}>{elem[col]}</td>
                                            ))}
                                            <td className="px-6 py-4">
                                                <a href={route(editAction, elem.id)} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                                <button onClick={() => handleDelete(elem.id)} className="ml-4 font-medium text-red-600 dark:text-red-500 hover:underline">Eliminar</button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>

                        <div className="flex justify-between items-center mt-4 text-xs">
                            <Button
                                variant="outlined"
                                onClick={() => handlePageChange(currentPage - 1)}
                                disabled={currentPage === 1}
                            >
                                Previous
                            </Button>
                            <span>
                                Página {currentPage} de {totalPages}
                            </span>
                            <Button
                                variant="outlined"
                                onClick={() => handlePageChange(currentPage + 1)}
                                disabled={currentPage === totalPages}
                            >
                                Next
                            </Button>
                        </div>
                        
                    </div>

                </div>
            </div>
        </div>
    )
}

export default Table